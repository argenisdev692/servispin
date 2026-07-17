<?php

namespace Tests\Feature\RemoteAssistance;

use App\Mail\RemoteAssistanceConfirmed;
use App\Models\Appointment;
use App\Models\CompanyData;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Fase D2 (T031e): US-6 — alta de cita remota desde el admin.
 *
 * El cliente llama por teléfono, paga por QR y Cesar la da de alta sin pasar por
 * la web.
 */
class AdminBookingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        // Objeto Role, no el nombre: con el string, Spatie lo resolvería contra
        // el guard 'web' por defecto y no existe. Ver PaymentVerificationTest.
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'sanctum']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->service = Service::factory()->remote()->create(['duration' => 30]);

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

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'service_id' => $this->service->id,
            'client_first_name' => 'Luis',
            'client_last_name' => 'Ramirez',
            'client_email' => 'luis@example.com',
            'client_phone' => '+34600999888',
            'issue_description' => 'El lavavajillas no desagua.',
            'start_time' => now()->addDays(2)->setTime(11, 0)->format('Y-m-d H:i:s'),
            'client_timezone' => 'Europe/Madrid',
            'payment_verified' => true,
            'payment_reference' => 'SUMUP-1234-AB',
            'payment_amount' => 30.00,
            'payer_name' => 'Luis Ramirez',
            'meeting_url' => 'https://meet.google.com/xyz-1234-abc',
        ], $overrides);
    }

    #[Test]
    public function cesar_puede_crear_una_cita_ya_pagada_confirmada_y_con_enlace_en_un_paso(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.appointments.remote.store'), $this->payload())
            ->assertStatus(201)
            ->assertJson(['success' => true]);

        $appointment = Appointment::first();

        $this->assertSame(Appointment::MODALITY_REMOTE, $appointment->modality);
        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertSame(Appointment::PAYMENT_VERIFIED, $appointment->payment_status);
        $this->assertSame('https://meet.google.com/xyz-1234-abc', $appointment->meeting_url);

        // FR-5: queda registrado que fue Cesar quien verificó el pago.
        $this->assertSame($this->admin->id, $appointment->payment_verified_by);
        $this->assertNotNull($appointment->payment_verified_at);

        // FR-11: sigue sin necesitar dirección.
        $this->assertNull($appointment->address);

        Mail::assertSent(RemoteAssistanceConfirmed::class, function ($mail) {
            return str_contains($mail->render(), 'meet.google.com/xyz-1234-abc');
        });
    }

    /**
     * FR-3 aplica IGUAL que en el flujo público (T031c).
     *
     * El atajo del admin no puede ser una puerta trasera al control de pago: si
     * Cesar no marca la cita como cobrada, no hay enlace. Si esto se rompe, la
     * regla del pago deja de valer para la mitad de las citas del sistema.
     */
    #[Test]
    public function una_cita_sin_marcar_como_pagada_queda_pendiente_y_sin_enlace(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.appointments.remote.store'), $this->payload([
                'payment_verified' => false,
                'meeting_url' => null,
            ]))
            ->assertStatus(201);

        $appointment = Appointment::first();

        $this->assertSame(Appointment::STATUS_PENDING, $appointment->status);
        $this->assertSame(Appointment::PAYMENT_CLAIMED, $appointment->payment_status);
        $this->assertNull($appointment->meeting_url);
        $this->assertNull($appointment->payment_verified_by);

        // Y sobre todo: nadie recibe un enlace.
        Mail::assertNotSent(RemoteAssistanceConfirmed::class);
    }

    #[Test]
    public function no_se_puede_crear_una_cita_pagada_sin_enlace_con_proveedor_manual(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.appointments.remote.store'), $this->payload(['meeting_url' => null]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('meeting_url');

        $this->assertSame(0, Appointment::count());
    }

    #[Test]
    public function un_hueco_ocupado_se_rechaza_tambien_desde_el_admin(): void
    {
        // FR-7: el atajo del admin no puede meter dos citas en el mismo hueco.
        $start = now()->addDays(2)->setTime(11, 0);

        Appointment::factory()->create([
            'start_time' => $start,
            'end_time' => $start->copy()->addMinutes(60),
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.appointments.remote.store'), $this->payload([
                'start_time' => $start->format('Y-m-d H:i:s'),
            ]))
            ->assertStatus(422);

        $this->assertSame(0, Appointment::remote()->count());
    }

    #[Test]
    public function sin_autenticacion_no_se_puede_crear_una_cita_remota(): void
    {
        $this->postJson(route('admin.appointments.remote.store'), $this->payload())
            ->assertStatus(401);

        $this->assertSame(0, Appointment::count());
    }

    #[Test]
    public function un_usuario_normal_no_puede_crear_una_cita_remota(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'User', 'guard_name' => 'sanctum']));

        $this->actingAs($user)
            ->postJson(route('admin.appointments.remote.store'), $this->payload())
            ->assertStatus(403);

        $this->assertSame(0, Appointment::count());
    }

    #[Test]
    public function el_guardarrail_pci_tambien_aplica_al_alta_del_admin(): void
    {
        // FR-4: que la petición venga de un admin no la saca del alcance de PCI.
        $this->actingAs($this->admin)
            ->postJson(route('admin.appointments.remote.store'), $this->payload([
                'card_number' => '4111111111111111',
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('card_number');

        $this->assertSame(0, Appointment::count());
    }

    #[Test]
    public function los_datos_del_cliente_se_escriben_a_mano_sin_lista(): void
    {
        // US-6: el cliente no existe en el sistema y no hay select de donde elegirlo.
        $this->actingAs($this->admin)
            ->postJson(route('admin.appointments.remote.store'), $this->payload())
            ->assertStatus(201);

        $this->assertDatabaseHas('appointments', [
            'client_email' => 'luis@example.com',
            'client_first_name' => 'Luis',
        ]);
    }
}
