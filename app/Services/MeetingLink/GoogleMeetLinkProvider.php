<?php

namespace App\Services\MeetingLink;

use App\Models\Appointment;
use Illuminate\Support\Facades\Log;
use Spatie\GoogleCalendar\Event;
use Throwable;

/**
 * Genera el enlace de la videollamada creando un evento con Google Meet.
 *
 * Usa `spatie/laravel-google-calendar` + `addMeetLink()`, autenticando **por
 * OAuth como usuario** (la cuenta de Cesar), no con service account. Esa es la
 * diferencia que permite usar un Gmail gratuito: la restricción
 * `Invalid conference type value` afecta solo a las service accounts sobre
 * calendarios personales (research #3c y #6b).
 *
 * ⚠️ Este provider LANZA `MeetingLinkException` cuando algo va mal, y nunca
 * devuelve null silencioso. El controlador (`resolveMeetingLink`) captura esa
 * excepción y confirma la cita IGUALMENTE, marcándola para enlace manual
 * (FR-15). El dinero ya entró: un fallo de Google no puede costar una cita.
 */
class GoogleMeetLinkProvider implements MeetingLinkProvider
{
    public function linkFor(Appointment $appointment): ?string
    {
        $this->guardAuthProfile();

        try {
            $event = $this->createGoogleEvent($appointment);
        } catch (Throwable $e) {
            // Token revocado a los 7 días (R-6), API caída, cuota... da igual:
            // se envuelve y se deja subir para que el controlador lo absorba.
            throw new MeetingLinkException(
                "No se pudo crear el evento de Google Meet: {$e->getMessage()}",
                previous: $e
            );
        }

        $link = $event->googleEvent->getHangoutLink();

        if (! $link) {
            // El evento se creó pero sin enlace de Meet. Es el síntoma típico de
            // estar en perfil service_account (research #3c): Google acepta el
            // evento y descarta la conferencia. Se trata como fallo, no como
            // "enlace manual pendiente".
            throw new MeetingLinkException(
                'Google creó el evento pero no devolvió enlace de Meet. '
                .'Casi siempre es el perfil service_account: usa GOOGLE_CALENDAR_AUTH_PROFILE=oauth.'
            );
        }

        // Se guardan para poder editar o borrar el evento después (FR-14). El
        // controlador es quien persiste el modelo dentro de su transacción.
        $appointment->google_event_id = $event->googleEvent->getId();
        $appointment->google_calendar_id = config('google-calendar.calendar_id');
        $appointment->meeting_provider = $this->name();

        return $link;
    }

    public function isAutomatic(): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'google_meet';
    }

    /**
     * Crea el evento en Google Calendar con enlace de Meet e invita al cliente.
     *
     * Aislado en su propio método protegido para que los tests puedan
     * sobreescribirlo sin tocar la red: la lógica de este provider (persistir
     * ids, lanzar si no hay enlace) se prueba sin fingir el SDK de Google, y la
     * integración real se valida con la prueba de humo manual T011d.
     *
     * El start/end se pasan como Carbon en el huso de la aplicación
     * (Atlantic/Canary), que es como viven en `start_time`. Spatie toma el huso
     * del propio Carbon, así que Google muestra la hora correcta sin conversión
     * a UTC (plan §9 R-5).
     *
     * El cliente se añade como asistente con cualquier email válido (Gmail,
     * Outlook, Yahoo…). Google envía la invitación ICS; no hace falta que tenga
     * cuenta Google para unirse al Meet (entra como invitado en el navegador).
     * `sendUpdates=all` es lo que dispara ese email de invitación de Calendar.
     */
    protected function createGoogleEvent(Appointment $appointment): Event
    {
        $clientName = trim(
            ($appointment->client_first_name ?? '').' '.($appointment->client_last_name ?? '')
        );

        $event = new Event;
        $event->name = 'Asistencia remota — '.($clientName !== '' ? $clientName : 'cliente');
        $event->description = $appointment->issue_description ?? '';
        $event->startDateTime = $appointment->start_time;
        $event->endDateTime = $appointment->end_time;

        $this->addClientAsAttendee($event, $appointment, $clientName);
        $event->addMeetLink();

        // sendUpdates=all → Google notifica al asistente (Gmail, Outlook, etc.)
        return $event->save('insertEvent', ['sendUpdates' => 'all']);
    }

    /**
     * Invita al email del cliente al evento (Calendar + Meet), si es válido.
     */
    protected function addClientAsAttendee(Event $event, Appointment $appointment, string $clientName): void
    {
        $email = strtolower(trim((string) ($appointment->client_email ?? '')));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('GoogleMeetLinkProvider: cita sin email de cliente válido; evento sin invitación.', [
                'appointment_id' => $appointment->id,
            ]);

            return;
        }

        $event->addAttendee([
            'email' => $email,
            'name' => $clientName !== '' ? $clientName : $email,
            'responseStatus' => 'needsAction',
        ]);
    }

    /**
     * Aviso temprano si el perfil está en service_account: sin esto, el fallo
     * llega como un `Invalid conference type value` que no menciona la causa por
     * ningún lado (research #3c). No aborta —quizá alguien tiene Workspace con
     * delegación—, solo deja rastro claro en el log.
     */
    private function guardAuthProfile(): void
    {
        if (config('google-calendar.default_auth_profile') === 'service_account') {
            Log::warning(
                'GoogleMeetLinkProvider: el perfil de google-calendar es "service_account". '
                .'Con un Gmail personal esto devuelve "Invalid conference type value" y no crea el Meet. '
                .'Pon GOOGLE_CALENDAR_AUTH_PROFILE=oauth en el .env (research #6b).'
            );
        }
    }
}
