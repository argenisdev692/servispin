<?php

namespace Tests\Unit\RemoteAssistance;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase A (T007): scopes y casts del modelo para la modalidad remota.
 */
class AppointmentRemoteScopesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function las_citas_existentes_son_presenciales_por_defecto(): void
    {
        // La migración es aditiva: nada de lo que ya había puede cambiar de
        // comportamiento al añadir la modalidad.
        $appointment = Appointment::factory()->create();

        $this->assertSame(Appointment::MODALITY_ONSITE, $appointment->modality);
        $this->assertFalse($appointment->isRemote());
        $this->assertSame(Appointment::PAYMENT_UNPAID, $appointment->payment_status);
    }

    #[Test]
    public function el_scope_remote_solo_devuelve_citas_remotas(): void
    {
        Appointment::factory()->count(3)->create();
        Appointment::factory()->remote()->count(2)->create();

        $this->assertCount(2, Appointment::remote()->get());
        $this->assertCount(3, Appointment::onsite()->get());
    }

    #[Test]
    public function pending_payment_verification_es_la_bandeja_de_cesar(): void
    {
        // Solo debe salir la remota con pago declarado sin verificar.
        $claimed = Appointment::factory()->remote()->create();
        Appointment::factory()->remote()->paymentVerified()->create();
        Appointment::factory()->create(); // presencial, irrelevante

        $bandeja = Appointment::pendingPaymentVerification()->get();

        $this->assertCount(1, $bandeja);
        $this->assertTrue($bandeja->first()->is($claimed));
    }

    #[Test]
    public function awaiting_manual_link_encuentra_las_citas_que_perdieron_el_enlace(): void
    {
        // FR-15: si el provider automático falló, la cita está confirmada pero
        // sin enlace y Cesar tiene que pegarlo a mano. Esa cita no puede perderse.
        $rota = Appointment::factory()->remote()->confirmed()->paymentVerified()->create([
            'meeting_link_failed_at' => now(),
            'meeting_url' => null,
        ]);
        Appointment::factory()->remote()->confirmed()->paymentVerified()->create([
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
        ]);

        $pendientes = Appointment::awaitingManualLink()->get();

        $this->assertCount(1, $pendientes);
        $this->assertTrue($pendientes->first()->is($rota));
    }

    #[Test]
    public function los_campos_de_pago_y_enlace_se_castean(): void
    {
        $verifier = User::factory()->create();

        $appointment = Appointment::factory()->remote()->create([
            'payment_verified_by' => $verifier->id,
            'payment_verified_at' => now(),
            'meeting_link_failed_at' => now(),
            'payment_amount' => 30.5,
        ]);

        $this->assertInstanceOf(Carbon::class, $appointment->payment_claimed_at);
        $this->assertInstanceOf(Carbon::class, $appointment->payment_verified_at);
        $this->assertInstanceOf(Carbon::class, $appointment->meeting_link_failed_at);
        $this->assertSame('30.50', (string) $appointment->payment_amount);
    }

    #[Test]
    public function payment_verifier_registra_quien_dejo_pasar_la_llamada(): void
    {
        // FR-5: la pregunta "¿quién verificó este pago?" tiene que tener respuesta.
        $cesar = User::factory()->create();

        $appointment = Appointment::factory()->remote()->paymentVerified()->create([
            'payment_verified_by' => $cesar->id,
        ]);

        $this->assertTrue($appointment->paymentVerifier->is($cesar));
        $this->assertTrue($appointment->isPaymentVerified());
    }

    #[Test]
    public function una_cita_remota_no_necesita_direccion(): void
    {
        // FR-11: no se pide dirección postal en remoto.
        $appointment = Appointment::factory()->remote()->create();

        $this->assertNull($appointment->address);
    }

    #[Test]
    public function el_scope_active_de_service_desbloquea_el_listado_de_servicios(): void
    {
        // research #5: getServices() filtra por 'active', una columna que no
        // existía y hacía reventar GET /appointments/services.
        Service::query()->delete();

        Service::factory()->count(2)->create();
        Service::factory()->inactive()->create();
        Service::factory()->remote()->create();

        $this->assertCount(3, Service::active()->get());
        $this->assertCount(1, Service::remote()->get());
    }
}
