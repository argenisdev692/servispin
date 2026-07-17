<?php

namespace Tests\Feature\RemoteAssistance;

use App\Models\Appointment;
use App\Models\AvailabilityRule;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase C (T015b): el buffer de desplazamiento depende de la modalidad.
 *
 * research #9b: AvailabilityController aplicaba 240 min de buffer alrededor de
 * CADA cita, porque el técnico conduce. Heredarlo en las remotas haría que una
 * videollamada de 30 min bloquease 8,5 h de agenda — justo el "tiempo muerto
 * entre desplazamientos" que spec §2 quiere monetizar. El módulo anularía su
 * propia razón de existir.
 */
class BufferTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $date;

    protected function setUp(): void
    {
        parent::setUp();

        // Un día laborable cualquiera, con la jornada estándar del seeder.
        $this->date = now()->addDays(3)->setTime(0, 0);

        AvailabilityRule::create([
            'uuid' => (string) Str::uuid(),
            'day_of_week' => $this->date->dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '18:00:00',
            'is_available' => true,
        ]);
    }

    private function slotsFor(Service $service, ?string $timezone = null): array
    {
        $response = $this->postJson(route('remote-assistance.slots'), array_filter([
            'service_id' => $service->id,
            'date' => $this->date->format('Y-m-d'),
            'timezone' => $timezone,
        ]));

        $response->assertStatus(200);

        return collect($response->json('data'))->pluck('formatted_time')->all();
    }

    private function appointmentAt(string $time, int $minutes, string $modality): Appointment
    {
        $start = $this->date->copy()->setTimeFromTimeString($time);

        return Appointment::factory()->create([
            'start_time' => $start,
            'end_time' => $start->copy()->addMinutes($minutes),
            'status' => Appointment::STATUS_CONFIRMED,
            'modality' => $modality,
        ]);
    }

    /**
     * NO-REGRESIÓN: el flujo presencial no puede cambiar de comportamiento.
     *
     * Una visita presencial de 12:00 a 13:00 bloquea de 08:00 a 17:00 (4 h por
     * delante y por detrás), lo que en una jornada de 08:00-18:00 no deja ni un
     * hueco para otra presencial. Es el comportamiento que hay hoy en producción
     * y este módulo NO lo toca.
     */
    #[Test]
    public function una_presencial_sigue_bloqueando_el_dia_entero_para_otra_presencial(): void
    {
        $onsiteService = Service::factory()->create(['is_remote' => false, 'duration' => 60]);
        $this->appointmentAt('12:00', 60, Appointment::MODALITY_ONSITE);

        $this->assertSame([], $this->slotsFor($onsiteService), 'El buffer de 240 min entre presenciales ha cambiado.');
    }

    #[Test]
    public function una_videollamada_no_bloquea_ocho_horas_y_media_de_agenda(): void
    {
        // El bug que research #9b evita: con el buffer heredado de 240 min, esta
        // sesión de 30 min habría dejado el día sin un solo hueco.
        $remoteService = Service::factory()->remote()->create(['duration' => 30]);
        $this->appointmentAt('12:00', 30, Appointment::MODALITY_REMOTE);

        $slots = $this->slotsFor($remoteService);

        $this->assertNotEmpty($slots, 'Una videollamada de 30 min está vaciando la agenda del día.');
        $this->assertContains('09:00', $slots);
        $this->assertContains('15:00', $slots);

        // El hueco de la propia cita sí está ocupado: eso es FR-7, no el buffer.
        $this->assertNotContains('12:00', $slots);
    }

    #[Test]
    public function dos_videollamadas_caben_en_el_mismo_dia(): void
    {
        // Nadie se mueve de su sitio entre una llamada y la siguiente.
        $remoteService = Service::factory()->remote()->create(['duration' => 30]);
        $this->appointmentAt('10:00', 30, Appointment::MODALITY_REMOTE);
        $this->appointmentAt('12:00', 30, Appointment::MODALITY_REMOTE);

        $slots = $this->slotsFor($remoteService);

        $this->assertContains('11:00', $slots, 'Dos remotas deberían poder convivir con hueco entre ellas.');
        $this->assertContains('16:00', $slots);
    }

    #[Test]
    public function entre_presencial_y_remota_se_respeta_un_margen_reducido(): void
    {
        // El técnico necesita llegar a un sitio con conexión, pero no 4 horas.
        $remoteService = Service::factory()->remote()->create(['duration' => 30]);
        $this->appointmentAt('12:00', 60, Appointment::MODALITY_ONSITE);

        $slots = $this->slotsFor($remoteService);

        // Buffer mixto de 60 min → zona bloqueada 11:00-14:00.
        $this->assertNotContains('11:30', $slots);
        $this->assertNotContains('13:00', $slots);

        // Fuera de esa franja, la agenda sigue viva.
        $this->assertContains('09:00', $slots);
        $this->assertContains('15:00', $slots);
    }

    #[Test]
    public function el_margen_mixto_es_configurable(): void
    {
        config(['remote_assistance.buffer.mixed_minutes' => 0]);

        $remoteService = Service::factory()->remote()->create(['duration' => 30]);
        $this->appointmentAt('12:00', 60, Appointment::MODALITY_ONSITE);

        $slots = $this->slotsFor($remoteService);

        // Sin margen mixto, lo único bloqueado es la propia franja de la cita.
        $this->assertContains('10:00', $slots);
        $this->assertContains('14:00', $slots);
    }

    #[Test]
    public function los_slots_se_devuelven_en_el_huso_del_cliente_y_lo_indican(): void
    {
        // FR-6: nunca una hora sin decir de qué huso es.
        $remoteService = Service::factory()->remote()->create(['duration' => 30]);

        $response = $this->postJson(route('remote-assistance.slots'), [
            'service_id' => $remoteService->id,
            'date' => $this->date->format('Y-m-d'),
            'timezone' => 'America/Argentina/Buenos_Aires',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'timezone' => 'America/Argentina/Buenos_Aires',
                'business_timezone' => 'Atlantic/Canary',
                'timezone_matches_business' => false,
            ]);

        $first = $response->json('data.0');

        $this->assertArrayHasKey('client_formatted_time', $first);
        $this->assertArrayHasKey('client_offset', $first);
        // 'time' sigue siendo el del negocio: es lo que se reenvía en start_time
        // y lo que el flujo presencial ya consume (plan §9 R-5).
        $this->assertArrayHasKey('time', $first);
    }

    #[Test]
    public function sin_huso_del_cliente_se_cae_al_del_negocio_y_se_avisa(): void
    {
        $remoteService = Service::factory()->remote()->create(['duration' => 30]);

        $this->postJson(route('remote-assistance.slots'), [
            'service_id' => $remoteService->id,
            'date' => $this->date->format('Y-m-d'),
        ])
            ->assertStatus(200)
            ->assertJson([
                'timezone' => 'Atlantic/Canary',
                'timezone_matches_business' => true,
            ]);
    }
}
