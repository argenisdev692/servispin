<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

/**
 * Reglas de agenda compartidas entre citas presenciales y remotas.
 *
 * Existe porque la agenda del técnico es UNA SOLA (plan §1): las citas remotas
 * y las presenciales compiten por las mismas horas, así que la regla de quién
 * cabe dónde no puede vivir duplicada en dos controladores.
 *
 * Aquí conviven dos conceptos que conviene NO confundir:
 *
 *   - `conflictFor()`  → solapamiento duro. Dos citas no pueden ocupar el mismo
 *                        hueco, sean de la modalidad que sean (FR-7).
 *   - `bufferMinutesBetween()` → tiempo de desplazamiento entre dos citas.
 *                        No es un conflicto: es el margen que hace falta para
 *                        llegar. Solo afecta a qué huecos se OFRECEN.
 */
class SchedulingService
{
    /**
     * Devuelve la cita que choca con esta franja, o null si el hueco está libre.
     *
     * Modality-agnóstico a propósito: FR-7 dice "dos citas (remotas o
     * presenciales)". El técnico no puede estar en una videollamada y en casa de
     * un cliente a la vez.
     *
     * Replica exactamente la consulta que ya hacía AppointmentController@store,
     * incluida la de ignorar las canceladas y completadas.
     */
    public function conflictFor(Carbon $start, Carbon $end, ?int $excludeAppointmentId = null): ?Appointment
    {
        return Appointment::query()
            ->when($excludeAppointmentId, fn ($q) => $q->where('id', '!=', $excludeAppointmentId))
            ->where(function ($query) use ($start, $end) {
                $query->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);
            })
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->first();
    }

    public function hasConflict(Carbon $start, Carbon $end, ?int $excludeAppointmentId = null): bool
    {
        return $this->conflictFor($start, $end, $excludeAppointmentId) !== null;
    }

    /**
     * Minutos de margen que hay que dejar entre una cita existente y una nueva,
     * según el par de modalidades (research #9b, plan §4b).
     *
     * El buffer histórico de 240 min existe porque el técnico CONDUCE. Aplicarlo
     * a una videollamada haría que una sesión de 30 min bloquease 8,5 h de
     * agenda — justo el tiempo muerto entre desplazamientos que este módulo
     * quiere monetizar (spec §2). El módulo anularía su propio objetivo.
     */
    public function bufferMinutesBetween(string $existingModality, string $newModality): int
    {
        $existingIsRemote = $existingModality === Appointment::MODALITY_REMOTE;
        $newIsRemote = $newModality === Appointment::MODALITY_REMOTE;

        // Dos videollamadas seguidas: nadie se mueve de su sitio.
        if ($existingIsRemote && $newIsRemote) {
            return (int) config('remote_assistance.buffer.remote_to_remote_minutes', 0);
        }

        // Mixto: el técnico necesita llegar a un sitio con conexión decente.
        if ($existingIsRemote || $newIsRemote) {
            return (int) config('remote_assistance.buffer.mixed_minutes', 60);
        }

        // Presencial ↔ presencial: el comportamiento de siempre, intacto.
        return self::ONSITE_BUFFER_MINUTES;
    }

    /**
     * Buffer histórico entre dos citas presenciales. Estaba hardcodeado en
     * AvailabilityController:169; se mueve aquí sin cambiar su valor.
     */
    public const ONSITE_BUFFER_MINUTES = 240;
}
