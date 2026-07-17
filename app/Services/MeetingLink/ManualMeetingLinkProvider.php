<?php

namespace App\Services\MeetingLink;

use App\Models\Appointment;

/**
 * El enlace lo escribe una persona (Cesar) y viaja en la petición.
 *
 * Cumple dos papeles distintos, y conviene no confundirlos:
 *
 *  1. **Fallback de FR-15.** Si Google falla —token revocado a los 7 días, API
 *     caída, cuota agotada— la cita pagada se confirma igual y Cesar pega el
 *     enlace. Sin esto, un fallo de Google costaría una cita ya cobrada.
 *
 *  2. **Modo por defecto del repo.** Un clon sin credenciales de Google tiene
 *     que arrancar y funcionar. Producción pone 'google_meet' en el .env.
 *
 * No genera nada: la URL ya viene en el modelo, puesta por el controlador desde
 * la petición validada. Es deliberadamente un pass-through, para que el
 * controlador trate igual a los dos proveedores.
 */
class ManualMeetingLinkProvider implements MeetingLinkProvider
{
    public function linkFor(Appointment $appointment): ?string
    {
        // null si Cesar aún no lo ha pegado. No es un error: la cita se
        // confirma igual y queda marcada como pendiente de enlace.
        return $appointment->meeting_url;
    }

    public function isAutomatic(): bool
    {
        return false;
    }

    public function name(): string
    {
        return 'manual';
    }
}
