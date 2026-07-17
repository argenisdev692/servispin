<?php

namespace Tests\Feature\RemoteAssistance;

use App\Mail\RemoteAssistanceConfirmed;
use App\Mail\RemoteAssistanceRejected;
use App\Models\Appointment;
use App\Models\CompanyData;
use App\Models\Service;
use App\Models\User;
use App\Services\MeetingLink\MeetingLinkException;
use App\Services\MeetingLink\MeetingLinkProvider;
use App\Services\SchedulingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Fase D (T030): US-2 — verificar el pago y confirmar.
 *
 * Con el flujo por QR no hay webhook: el criterio de Cesar cotejando SumUp es el
 * único control de pago del sistema (research #2). Estos tests protegen ese
 * control.
 */
class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        // Los roles se siembran con guard 'sanctum' (DatabaseSeeder), no 'web'.
        //
        // ⚠️ Se le pasa el OBJETO Role, no el nombre. `assignRole('Admin')`
        // resolvería el nombre con el guard por defecto del modelo —'web', el
        // primero de config/auth.php— y reventaría con RoleDoesNotExist. Es el
        // mismo desajuste de guards que obliga a VerifyPaymentRequest a llamar a
        // hasRole('Admin', 'sanctum') con el guard explícito. El DatabaseSeeder
        // ya lo hace así, por eso funciona.
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

    private function remoteAppointment(): Appointment
    {
        return Appointment::factory()
            ->remote()
            ->for(Service::factory()->remote())
            ->create();
    }

    #[Test]
    public function verificar_el_pago_confirma_la_cita_y_envia_el_enlace(): void
    {
        $appointment = $this->remoteAppointment();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), [
                'decision' => 'verify',
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $appointment->refresh();

        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertSame(Appointment::PAYMENT_VERIFIED, $appointment->payment_status);
        $this->assertSame('https://meet.google.com/abc-defg-hij', $appointment->meeting_url);

        // FR-5: tiene que quedar registrado quién dejó pasar esta llamada.
        $this->assertSame($this->admin->id, $appointment->payment_verified_by);
        $this->assertNotNull($appointment->payment_verified_at);

        // Ahora sí: el email lleva el enlace, porque un humano vio el pago.
        Mail::assertSent(RemoteAssistanceConfirmed::class, function (RemoteAssistanceConfirmed $mail) {
            return str_contains($mail->render(), 'meet.google.com/abc-defg-hij');
        });
    }

    #[Test]
    public function el_tecnico_tambien_recibe_el_enlace(): void
    {
        // US-3: quien atiende la llamada necesita el enlace igual que el cliente.
        $appointment = $this->remoteAppointment();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), [
                'decision' => 'verify',
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            ])->assertStatus(200);

        Mail::assertSent(RemoteAssistanceConfirmed::class, fn ($mail) => $mail->isForTechnician === true);
        Mail::assertSent(RemoteAssistanceConfirmed::class, fn ($mail) => $mail->isForTechnician === false);
    }

    #[Test]
    public function rechazar_cancela_la_cita_libera_el_hueco_y_no_manda_enlace(): void
    {
        $appointment = $this->remoteAppointment();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), [
                'decision' => 'reject',
                'reason' => 'No aparece ningún cobro con esa referencia.',
            ])
            ->assertStatus(200);

        $appointment->refresh();

        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->status);
        $this->assertSame(Appointment::PAYMENT_REJECTED, $appointment->payment_status);
        $this->assertNull($appointment->meeting_url);
        $this->assertSame($this->admin->id, $appointment->payment_verified_by);

        Mail::assertSent(RemoteAssistanceRejected::class, function (RemoteAssistanceRejected $mail) {
            $rendered = $mail->render();
            $this->assertStringNotContainsString('meet.google.com', $rendered);

            return str_contains($rendered, 'No aparece ningún cobro');
        });

        Mail::assertNotSent(RemoteAssistanceConfirmed::class);
    }

    #[Test]
    public function el_hueco_de_una_cita_rechazada_vuelve_a_estar_libre(): void
    {
        $appointment = $this->remoteAppointment();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), ['decision' => 'reject'])
            ->assertStatus(200);

        // Cancelada ⇒ deja de contar para el solapamiento.
        $scheduling = app(SchedulingService::class);
        $this->assertFalse($scheduling->hasConflict($appointment->start_time, $appointment->end_time));
    }

    /**
     * FR-5: nadie sin autenticar puede tocar el estado de un pago.
     */
    #[Test]
    public function sin_autenticacion_no_se_puede_verificar_un_pago(): void
    {
        $appointment = $this->remoteAppointment();

        $this->patchJson(route('admin.appointments.verify-payment', $appointment->id), [
            'decision' => 'verify',
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
        ])->assertStatus(401);

        $appointment->refresh();
        $this->assertSame(Appointment::PAYMENT_CLAIMED, $appointment->payment_status);
        $this->assertNull($appointment->meeting_url);
    }

    #[Test]
    public function un_usuario_normal_no_puede_verificar_un_pago(): void
    {
        // Estar registrado no es ser Cesar. US-2: el administrador es el único
        // que puede convertir una solicitud en cita confirmada.
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'User', 'guard_name' => 'sanctum']));

        $appointment = $this->remoteAppointment();

        $this->actingAs($user)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), [
                'decision' => 'verify',
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            ])
            ->assertStatus(403);

        $this->assertSame(Appointment::PAYMENT_CLAIMED, $appointment->fresh()->payment_status);
    }

    /**
     * FR-15 / plan §3: el fallo de Google no puede costar una cita ya cobrada.
     *
     * Este es el escenario de R-6: el refresh token se revoca a los 7 días si la
     * app quedó en "Testing". Pasa en producción, días después de dar el módulo
     * por terminado. Cuando pase, la cita se confirma igual.
     */
    #[Test]
    public function si_el_proveedor_automatico_falla_la_cita_se_confirma_igualmente(): void
    {
        $this->app->bind(MeetingLinkProvider::class, fn () => new class implements MeetingLinkProvider
        {
            public function linkFor(Appointment $appointment): ?string
            {
                throw new MeetingLinkException('Token de Google revocado');
            }

            public function isAutomatic(): bool
            {
                return true;
            }

            public function name(): string
            {
                return 'google_meet';
            }
        });

        $appointment = $this->remoteAppointment();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), ['decision' => 'verify'])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'data' => ['meeting_link_failed' => true]]);

        $appointment->refresh();

        // El dinero entró: la cita NO se pierde.
        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertSame(Appointment::PAYMENT_VERIFIED, $appointment->payment_status);
        $this->assertNull($appointment->meeting_url);

        // Y queda marcada para que Cesar pegue el enlace a mano.
        $this->assertNotNull($appointment->meeting_link_failed_at);
        $this->assertTrue(Appointment::awaitingManualLink()->where('id', $appointment->id)->exists());
    }

    #[Test]
    public function con_proveedor_manual_no_se_puede_confirmar_sin_pegar_el_enlace(): void
    {
        // Si no, el cliente recibiría un email de "confirmada" sin forma de entrar.
        $appointment = $this->remoteAppointment();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), ['decision' => 'verify'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('meeting_url');

        $this->assertSame(Appointment::PAYMENT_CLAIMED, $appointment->fresh()->payment_status);
    }

    #[Test]
    public function un_enlace_que_no_es_una_url_se_rechaza(): void
    {
        $appointment = $this->remoteAppointment();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), [
                'decision' => 'verify',
                'meeting_url' => 'pegar aquí el enlace',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('meeting_url');
    }

    #[Test]
    public function no_se_puede_verificar_dos_veces_la_misma_cita(): void
    {
        $appointment = $this->remoteAppointment();
        $appointment->update([
            'payment_status' => Appointment::PAYMENT_VERIFIED,
            'payment_verified_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $appointment->id), [
                'decision' => 'verify',
                'meeting_url' => 'https://meet.google.com/otro-enlace',
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function no_se_puede_verificar_el_pago_de_una_cita_presencial(): void
    {
        $onsite = Appointment::factory()->create();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', $onsite->id), [
                'decision' => 'verify',
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function una_cita_inexistente_devuelve_404(): void
    {
        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.verify-payment', 99999), [
                'decision' => 'verify',
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            ])
            ->assertStatus(404);
    }
}
