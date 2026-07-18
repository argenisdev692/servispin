<?php

namespace Tests\Feature\RemoteAssistance;

use App\Mail\AppointmentCancelled;
use App\Mail\RemoteAssistanceCancelled;
use App\Mail\RemoteAssistanceRejected;
use App\Models\Appointment;
use App\Models\CompanyData;
use App\Models\Service;
use App\Models\User;
use App\Services\SchedulingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Fase G: US-5 (cancelación con reembolso) y FR-12 (liberación del hueco).
 */
class CancellationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'sanctum']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        CompanyData::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Cesar Gonzalez',
            'company_name' => 'ServiSpin',
            'email' => 'info@servispin.net',
            'phone' => '+34643940970',
            'user_id' => $this->admin->id,
        ]);
    }

    // ---- US-5: cancelar una cita remota pagada ----

    #[Test]
    public function cancelar_una_remota_pagada_marca_reembolso_pendiente_y_avisa(): void
    {
        // US-5: el cliente pagó por adelantado; al cancelar hay que devolverle.
        $appointment = Appointment::factory()
            ->remote()->confirmed()->paymentVerified()
            ->for(Service::factory()->remote())
            ->create(['meeting_url' => 'https://meet.google.com/abc-defg-hij']);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointment.calendar.status.update', $appointment->id), [
                'status' => 'Cancelled',
            ])
            ->assertStatus(200)
            ->assertJson(['data' => ['refund_pending' => true]]);

        $this->assertSame(Appointment::PAYMENT_REFUND_PENDING, $appointment->fresh()->payment_status);

        Mail::assertSent(RemoteAssistanceCancelled::class, function (RemoteAssistanceCancelled $mail) {
            return $mail->refundPending === true && str_contains($mail->render(), 'reembolso');
        });
    }

    #[Test]
    public function el_hueco_de_una_remota_cancelada_vuelve_a_estar_libre(): void
    {
        // US-5: al cancelar, el hueco debe quedar disponible.
        $appointment = Appointment::factory()
            ->remote()->confirmed()->paymentVerified()
            ->for(Service::factory()->remote())
            ->create();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointment.calendar.status.update', $appointment->id), [
                'status' => 'Cancelled',
            ])->assertStatus(200);

        $scheduling = app(SchedulingService::class);
        $this->assertFalse($scheduling->hasConflict($appointment->start_time, $appointment->end_time));
    }

    #[Test]
    public function cancelar_una_remota_sin_pago_verificado_no_marca_reembolso(): void
    {
        // Si nunca se verificó el pago, no hay dinero que devolver.
        // Ya no se cancela por el endpoint genérico del calendario: va por verify-payment.
        $appointment = Appointment::factory()
            ->remote() // payment_status = claimed
            ->for(Service::factory()->remote())
            ->create();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointment.calendar.status.update', $appointment->id), [
                'status' => 'Cancelled',
            ])
            ->assertStatus(422);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), [
                'decision' => 'reject',
                'reason' => 'Pago no localizado en SumUp.',
            ])
            ->assertStatus(200)
            ->assertJson(['data' => ['payment_status' => Appointment::PAYMENT_REJECTED]]);

        $this->assertNotSame(Appointment::PAYMENT_REFUND_PENDING, $appointment->fresh()->payment_status);
        $this->assertSame(Appointment::PAYMENT_REJECTED, $appointment->fresh()->payment_status);
    }

    #[Test]
    public function cancelar_una_cita_presencial_sigue_usando_su_email_de_siempre(): void
    {
        // No-regresión: el flujo presencial no cambia.
        $appointment = Appointment::factory()->confirmed()->create();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointment.calendar.status.update', $appointment->id), [
                'status' => 'Cancelled',
            ])->assertStatus(200);

        Mail::assertSent(AppointmentCancelled::class);
        Mail::assertNotSent(RemoteAssistanceCancelled::class);
    }

    // ---- FR-12: liberación automática del hueco sin verificar ----

    #[Test]
    public function una_solicitud_no_verificada_pasado_el_plazo_se_libera(): void
    {
        $appointment = Appointment::factory()
            ->remote() // Pending + claimed
            ->for(Service::factory()->remote())
            ->create([
                'payment_claimed_at' => now()->subHours(25), // > 24 h
                'start_time' => now()->addDays(3)->setTime(10, 0),
                'end_time' => now()->addDays(3)->setTime(10, 45),
            ]);

        $this->artisan('appointments:release-unverified')->assertExitCode(0);

        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
        Mail::assertSent(RemoteAssistanceRejected::class);
    }

    #[Test]
    public function una_solicitud_reciente_no_se_libera(): void
    {
        // Declarada hace 1 h y la cita es dentro de días: Cesar aún tiene tiempo.
        $appointment = Appointment::factory()
            ->remote()
            ->for(Service::factory()->remote())
            ->create([
                'payment_claimed_at' => now()->subHour(),
                'start_time' => now()->addDays(5)->setTime(10, 0),
                'end_time' => now()->addDays(5)->setTime(10, 45),
            ]);

        $this->artisan('appointments:release-unverified')->assertExitCode(0);

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->fresh()->status);
        Mail::assertNothingSent();
    }

    #[Test]
    public function una_solicitud_cuya_cita_es_inminente_se_libera_aunque_sea_reciente(): void
    {
        // "Lo que ocurra antes": aunque se declaró hace nada, si faltan menos de
        // 2 h para la cita y sigue sin verificar, se libera.
        $appointment = Appointment::factory()
            ->remote()
            ->for(Service::factory()->remote())
            ->create([
                'payment_claimed_at' => now()->subMinutes(10),
                'start_time' => now()->addHour(), // < 2 h
                'end_time' => now()->addHour()->addMinutes(45),
            ]);

        $this->artisan('appointments:release-unverified')->assertExitCode(0);

        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
    }

    #[Test]
    public function una_cita_ya_verificada_nunca_se_libera_por_este_comando(): void
    {
        // Solo toca las 'claimed'. Una verificada y confirmada no se toca.
        $appointment = Appointment::factory()
            ->remote()->confirmed()->paymentVerified()
            ->for(Service::factory()->remote())
            ->create([
                'payment_claimed_at' => now()->subDays(2),
                'start_time' => now()->addHour(),
                'end_time' => now()->addHour()->addMinutes(45),
            ]);

        $this->artisan('appointments:release-unverified')->assertExitCode(0);

        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->fresh()->status);
    }
}
