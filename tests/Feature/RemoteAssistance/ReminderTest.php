<?php

namespace Tests\Feature\RemoteAssistance;

use App\Mail\AppointmentReminder;
use App\Mail\RemoteAssistanceReminder;
use App\Models\Appointment;
use App\Models\CompanyData;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase F (T040): US-3 — recordatorios de citas remotas.
 */
class ReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $admin = User::factory()->create();
        CompanyData::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Cesar Gonzalez',
            'company_name' => 'ServiSpin',
            'email' => 'info@servispin.net',
            'phone' => '+34643940970',
            'user_id' => $admin->id,
        ]);
    }

    private function confirmedRemoteAt(\DateTimeInterface $start): Appointment
    {
        $start = Carbon::instance($start);

        return Appointment::factory()
            ->remote()
            ->confirmed()
            ->paymentVerified()
            ->for(Service::factory()->remote())
            ->create([
                'start_time' => $start,
                'end_time' => (clone $start)->addMinutes(45),
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            ]);
    }

    // ---- Recordatorio diario (24 h) ----

    #[Test]
    public function la_cita_remota_de_manana_recibe_recordatorio_con_enlace_no_el_presencial(): void
    {
        // El bug que esto evita: como lo remoto vive en la misma tabla, el comando
        // diario recogía la cita remota y le mandaba el AppointmentReminder
        // presencial, sin enlace. US-3 exige el enlace.
        $this->confirmedRemoteAt(now()->addDay()->setTime(10, 0));

        $this->artisan('appointments:send-reminders')->assertExitCode(0);

        Mail::assertSent(RemoteAssistanceReminder::class, function (RemoteAssistanceReminder $mail) {
            return str_contains($mail->render(), 'meet.google.com/abc-defg-hij');
        });
        // Y NO el recordatorio presencial.
        Mail::assertNotSent(AppointmentReminder::class);
    }

    #[Test]
    public function el_tecnico_tambien_recibe_el_recordatorio_diario(): void
    {
        // US-3: quien atiende la llamada también necesita el aviso.
        $this->confirmedRemoteAt(now()->addDay()->setTime(10, 0));

        $this->artisan('appointments:send-reminders')->assertExitCode(0);

        Mail::assertSent(RemoteAssistanceReminder::class, fn ($m) => $m->isForTechnician === true);
        Mail::assertSent(RemoteAssistanceReminder::class, fn ($m) => $m->isForTechnician === false);
    }

    #[Test]
    public function una_cita_remota_cancelada_no_recibe_recordatorio(): void
    {
        // US-3: si la cita fue cancelada, no se envía ningún recordatorio.
        $start = now()->addDay()->setTime(10, 0);
        Appointment::factory()->remote()->cancelled()->create([
            'start_time' => $start,
            'end_time' => $start->copy()->addMinutes(45),
        ]);

        $this->artisan('appointments:send-reminders')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    // ---- Recordatorio inminente (30 min) ----

    #[Test]
    public function la_cita_que_empieza_en_media_hora_recibe_el_recordatorio_inminente(): void
    {
        $this->confirmedRemoteAt(now()->addMinutes(28));

        $this->artisan('appointments:send-imminent-reminders')->assertExitCode(0);

        Mail::assertSent(RemoteAssistanceReminder::class, function (RemoteAssistanceReminder $mail) {
            return $mail->when === RemoteAssistanceReminder::WHEN_IMMINENT
                && str_contains($mail->render(), 'meet.google.com/abc-defg-hij');
        });
    }

    #[Test]
    public function el_recordatorio_inminente_no_se_duplica_al_ejecutar_dos_veces(): void
    {
        // El comando corre cada 5 min: la misma cita cae en varias pasadas. Sin
        // idempotencia, el cliente recibiría el aviso una y otra vez.
        $appointment = $this->confirmedRemoteAt(now()->addMinutes(28));

        $this->artisan('appointments:send-imminent-reminders');
        $this->artisan('appointments:send-imminent-reminders');
        $this->artisan('appointments:send-imminent-reminders');

        // Un solo envío al cliente en total, pese a tres ejecuciones.
        Mail::assertSent(
            RemoteAssistanceReminder::class,
            fn ($m) => $m->when === RemoteAssistanceReminder::WHEN_IMMINENT && ! $m->isForTechnician
        );
        Mail::assertSentCount(2); // cliente + técnico, una vez

        $this->assertNotNull($appointment->fresh()->imminent_reminder_sent_at);
    }

    #[Test]
    public function una_cita_cancelada_no_recibe_recordatorio_inminente(): void
    {
        Appointment::factory()->remote()->cancelled()->create([
            'start_time' => now()->addMinutes(28),
            'end_time' => now()->addMinutes(73),
        ]);

        $this->artisan('appointments:send-imminent-reminders')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    #[Test]
    public function una_cita_lejana_no_recibe_el_recordatorio_inminente(): void
    {
        // Dentro de 3 horas todavía no toca el aviso de 30 min.
        $this->confirmedRemoteAt(now()->addHours(3));

        $this->artisan('appointments:send-imminent-reminders')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    #[Test]
    public function una_cita_presencial_inminente_no_recibe_este_recordatorio(): void
    {
        // El de 30 min es solo para remotas: una visita presencial no se recuerda
        // así (y el buffer de desplazamiento hace que 30 min no tenga sentido).
        Appointment::factory()->confirmed()->create([
            'start_time' => now()->addMinutes(28),
            'end_time' => now()->addMinutes(88),
        ]);

        $this->artisan('appointments:send-imminent-reminders')->assertExitCode(0);

        Mail::assertNothingSent();
    }
}
