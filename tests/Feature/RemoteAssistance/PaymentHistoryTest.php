<?php

namespace Tests\Feature\RemoteAssistance;

use App\Mail\RemoteAssistanceConfirmed;
use App\Models\Appointment;
use App\Models\AppointmentPaymentEvent;
use App\Models\CompanyData;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentHistoryTest extends TestCase
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

        config(['remote_assistance.meeting_provider' => 'manual']);
    }

    #[Test]
    public function verificar_pago_registra_eventos_en_el_historial(): void
    {
        $appointment = Appointment::factory()
            ->remote()
            ->for(Service::factory()->remote())
            ->create(['payment_reference' => 'SUMUP-9999']);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), [
                'decision' => 'verify',
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('appointment_payment_events', [
            'appointment_id' => $appointment->id,
            'event_type' => AppointmentPaymentEvent::TYPE_VERIFIED,
            'reference' => 'SUMUP-9999',
            'recorded_by' => $this->admin->id,
        ]);
    }

    #[Test]
    public function se_puede_anadir_enlace_manual_y_reenviar_email(): void
    {
        $appointment = Appointment::factory()
            ->remote()
            ->for(Service::factory()->remote())
            ->paymentVerified()
            ->confirmed()
            ->create([
                'meeting_link_failed_at' => now(),
                'meeting_url' => null,
            ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.meeting-link', $appointment->id), [
                'meeting_url' => 'https://meet.google.com/manual-link',
                'resend_email' => true,
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $appointment->refresh();
        $this->assertSame('https://meet.google.com/manual-link', $appointment->meeting_url);
        $this->assertNull($appointment->meeting_link_failed_at);

        $this->assertDatabaseHas('appointment_payment_events', [
            'appointment_id' => $appointment->id,
            'event_type' => AppointmentPaymentEvent::TYPE_LINK_ADDED,
        ]);

        Mail::assertSent(RemoteAssistanceConfirmed::class);
    }

    #[Test]
    public function el_historial_de_pagos_es_accesible_y_filtra_por_referencia(): void
    {
        $appointment = Appointment::factory()
            ->remote()
            ->for(Service::factory()->remote())
            ->create(['payment_reference' => 'UNIQUE-REF-XYZ']);

        AppointmentPaymentEvent::create([
            'appointment_id' => $appointment->id,
            'event_type' => AppointmentPaymentEvent::TYPE_CLAIMED,
            'reference' => 'UNIQUE-REF-XYZ',
            'amount' => 30,
            'currency' => 'EUR',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.remote-assistance.payments', ['reference' => 'UNIQUE-REF']))
            ->assertStatus(200)
            ->assertSee('UNIQUE-REF-XYZ')
            ->assertSee('Pago declarado');
    }

    #[Test]
    public function se_puede_reenviar_la_confirmacion_si_hay_enlace(): void
    {
        $appointment = Appointment::factory()
            ->remote()
            ->for(Service::factory()->remote())
            ->paymentVerified()
            ->confirmed()
            ->create(['meeting_url' => 'https://meet.google.com/existing']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.appointments.resend-confirmation', $appointment->id))
            ->assertStatus(200);

        Mail::assertSent(RemoteAssistanceConfirmed::class, 2);

        $this->assertDatabaseHas('appointment_payment_events', [
            'appointment_id' => $appointment->id,
            'event_type' => AppointmentPaymentEvent::TYPE_CONFIRMATION_RESENT,
        ]);
    }
}
