<?php

namespace App\Services\MeetingLink;

use App\Models\Appointment;

/**
 * Cómo se obtiene el enlace de videollamada de una cita remota confirmada (FR-8).
 *
 * Esta interfaz NO existe por purismo ni por extensibilidad: existe porque el
 * refresh token de Google es frágil (research #7) y una cita YA PAGADA no puede
 * perderse porque la API de Google falle (FR-15). El proveedor manual es la red
 * de seguridad, no una opción de segunda.
 *
 * Que además deje la puerta abierta a JaaS es un efecto secundario, no el motivo.
 */
interface MeetingLinkProvider
{
    /**
     * Devuelve el enlace de la videollamada para esta cita.
     *
     * @return string|null null significa "no hay enlace todavía; Cesar lo pegará
     *                     a mano". NUNCA es un error: una cita sin enlace se
     *                     confirma igual y se marca con meeting_link_failed_at.
     *
     * @throws MeetingLinkException si un proveedor automático no puede generarlo.
     *                              Quien llama DEBE capturarla y confirmar la cita igualmente (FR-15).
     */
    public function linkFor(Appointment $appointment): ?string;

    /**
     * ¿El enlace lo genera el sistema (true) o lo escribe una persona (false)?
     *
     * Lo consume la validación: con un proveedor manual, `meeting_url` es
     * obligatorio al verificar el pago, porque nadie lo va a generar por ti.
     */
    public function isAutomatic(): bool;

    /**
     * Identificador que se persiste en appointments.meeting_provider,
     * para saber después quién generó cada enlace.
     */
    public function name(): string;
}
