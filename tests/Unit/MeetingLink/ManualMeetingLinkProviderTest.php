<?php

namespace Tests\Unit\MeetingLink;

use App\Models\Appointment;
use App\Services\MeetingLink\ManualMeetingLinkProvider;
use App\Services\MeetingLink\MeetingLinkProvider;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fase B1 (T011): proveedor manual y resolución vía config.
 */
class ManualMeetingLinkProviderTest extends TestCase
{
    #[Test]
    public function devuelve_el_enlace_que_cesar_escribio_a_mano(): void
    {
        $provider = new ManualMeetingLinkProvider;
        $appointment = new Appointment(['meeting_url' => 'https://meet.google.com/abc-defg-hij']);

        $this->assertSame('https://meet.google.com/abc-defg-hij', $provider->linkFor($appointment));
    }

    #[Test]
    public function devuelve_null_si_todavia_no_hay_enlace_y_eso_no_es_un_error(): void
    {
        // null significa "pendiente de pegar a mano", no "ha fallado algo".
        // La cita se confirma igual: el dinero ya entró (FR-15).
        $provider = new ManualMeetingLinkProvider;

        $this->assertNull($provider->linkFor(new Appointment));
    }

    #[Test]
    public function no_es_automatico_y_por_eso_meeting_url_sera_obligatorio(): void
    {
        $provider = new ManualMeetingLinkProvider;

        $this->assertFalse($provider->isAutomatic());
        $this->assertSame('manual', $provider->name());
    }

    #[Test]
    public function la_config_decide_que_implementacion_se_inyecta(): void
    {
        config(['remote_assistance.meeting_provider' => 'manual']);

        $this->assertInstanceOf(
            ManualMeetingLinkProvider::class,
            $this->app->make(MeetingLinkProvider::class)
        );
    }

    #[Test]
    public function un_proveedor_desconocido_falla_ruidosamente(): void
    {
        // Un typo en el .env no puede degradar a manual en silencio: Cesar
        // acabaría pegando enlaces a mano sin saber por qué.
        config(['remote_assistance.meeting_provider' => 'zoom_typo']);
        $this->app->forgetInstance(MeetingLinkProvider::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('zoom_typo');

        $this->app->make(MeetingLinkProvider::class);
    }
}
