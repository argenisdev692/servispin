<?php

namespace Tests\Unit\MeetingLink;

use App\Models\Appointment;
use App\Services\MeetingLink\GoogleMeetLinkProvider;
use App\Services\MeetingLink\MeetingLinkException;
use PHPUnit\Framework\Attributes\Test;
use Spatie\GoogleCalendar\Event;
use Tests\TestCase;

/**
 * Fase B2 (T011e): la lógica del provider de Google, SIN llamar a Google.
 *
 * Lo que se prueba aquí es MI código: que persiste los ids, que lanza cuando no
 * hay enlace, que es automático. Que Google devuelva un Meet real es otra cosa
 * (T011d, prueba de humo manual con credenciales) — fingir el SDK entero probaría
 * el SDK, no este provider (plan §7).
 *
 * La creación del evento (`createGoogleEvent`) se sobreescribe con un doble para
 * no tocar la red.
 */
class GoogleMeetLinkProviderTest extends TestCase
{
    #[Test]
    public function es_automatico_y_se_identifica_como_google_meet(): void
    {
        $provider = new GoogleMeetLinkProvider;

        $this->assertTrue($provider->isAutomatic());
        $this->assertSame('google_meet', $provider->name());
    }

    #[Test]
    public function devuelve_el_enlace_y_persiste_los_ids_del_evento(): void
    {
        // FR-14: hay que poder editar/borrar el evento después, así que se
        // guardan el id del evento y el del calendario.
        config(['google-calendar.calendar_id' => 'servispin@gmail.com']);

        $provider = $this->providerReturning(
            hangoutLink: 'https://meet.google.com/abc-defg-hij',
            eventId: 'evt_12345'
        );

        $appointment = Appointment::factory()->remote()->make();

        $link = $provider->linkFor($appointment);

        $this->assertSame('https://meet.google.com/abc-defg-hij', $link);
        $this->assertSame('evt_12345', $appointment->google_event_id);
        $this->assertSame('servispin@gmail.com', $appointment->google_calendar_id);
        $this->assertSame('google_meet', $appointment->meeting_provider);
    }

    #[Test]
    public function lanza_excepcion_si_google_no_devuelve_enlace(): void
    {
        // El caso service_account: Google acepta el evento y descarta la
        // conferencia. Debe tratarse como fallo (→ FR-15), no como null.
        $provider = $this->providerReturning(hangoutLink: null, eventId: 'evt_x');

        $this->expectException(MeetingLinkException::class);

        $provider->linkFor(Appointment::factory()->remote()->make());
    }

    #[Test]
    public function envuelve_cualquier_fallo_de_google_en_meeting_link_exception(): void
    {
        // Token revocado, API caída, cuota: todo tiene que salir como
        // MeetingLinkException para que el controlador lo absorba y confirme
        // la cita igualmente (FR-15).
        $provider = new class extends GoogleMeetLinkProvider
        {
            protected function createGoogleEvent(Appointment $appointment): Event
            {
                throw new \RuntimeException('invalid_grant: Token has been expired or revoked');
            }
        };

        $this->expectException(MeetingLinkException::class);
        $this->expectExceptionMessage('Token has been expired or revoked');

        $provider->linkFor(Appointment::factory()->remote()->make());
    }

    /**
     * Provider con la creación del evento sustituida por un doble que devuelve
     * un Event con el hangoutLink y el id indicados.
     */
    private function providerReturning(?string $hangoutLink, string $eventId): GoogleMeetLinkProvider
    {
        return new class($hangoutLink, $eventId) extends GoogleMeetLinkProvider
        {
            public function __construct(private ?string $hangoutLink, private string $eventId) {}

            protected function createGoogleEvent(Appointment $appointment): Event
            {
                $googleEvent = new \Google\Service\Calendar\Event;
                $googleEvent->setId($this->eventId);
                $googleEvent->setHangoutLink($this->hangoutLink);

                $event = new Event;
                $event->googleEvent = $googleEvent;

                return $event;
            }
        };
    }
}
