<?php

namespace App\Console\Commands;

use App\Mail\RemoteAssistanceRejected;
use App\Models\Appointment;
use App\Models\CompanyData;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Libera el hueco de las solicitudes remotas que nadie ha verificado a tiempo
 * (FR-12, US-1).
 *
 * La verificación es manual y por tanto un cuello de botella humano (R-4): una
 * solicitud con pago declarado bloquea un hueco hasta que Cesar la mira. Si no la
 * mira, el hueco no puede quedar reservado para siempre. Este comando la cancela
 * y libera el hueco pasado el plazo.
 *
 * Plazo (D-4, propuesta del spec, configurable en remote_assistance.php):
 * 24 h desde que se declaró el pago, o 2 h antes de la cita, lo que ocurra antes.
 *
 * El cliente recibe el email de "no localizamos tu pago", que ya invita a
 * responder si de verdad pagó: así una cancelación automática no deja tirado a
 * alguien que sí pagó y a quien Cesar simplemente no llegó a tiempo.
 */
class ReleaseUnverifiedRemoteAppointments extends Command
{
    protected $signature = 'appointments:release-unverified';

    protected $description = 'Libera el hueco de las solicitudes remotas no verificadas a tiempo (FR-12)';

    public function handle(): int
    {
        $companyData = CompanyData::first();
        $now = Carbon::now();

        $holdHours = (int) config('remote_assistance.hold_hours', 24);
        $cutoffHours = (int) config('remote_assistance.hold_cutoff_hours_before', 2);

        // Candidatas: remotas, pendientes, con pago declarado pero sin verificar.
        $candidates = Appointment::remote()
            ->where('status', Appointment::STATUS_PENDING)
            ->where('payment_status', Appointment::PAYMENT_CLAIMED)
            ->get();

        $released = 0;

        foreach ($candidates as $appointment) {
            // "Lo que ocurra antes": o pasaron las 24 h desde la declaración, o
            // faltan menos de 2 h para la cita.
            $declaredDeadline = $appointment->payment_claimed_at
                ? $appointment->payment_claimed_at->copy()->addHours($holdHours)
                : $appointment->created_at->copy()->addHours($holdHours);

            $imminenceDeadline = $appointment->start_time->copy()->subHours($cutoffHours);

            $expired = $now->greaterThanOrEqualTo($declaredDeadline)
                || $now->greaterThanOrEqualTo($imminenceDeadline);

            if (! $expired) {
                continue;
            }

            // Cancelar libera el hueco: una cita cancelada deja de contar para el
            // solapamiento (FR-7). No es 'rejected' porque nadie ha dicho que el
            // pago sea falso; simplemente caducó el plazo de verificación.
            $appointment->status = Appointment::STATUS_CANCELLED;
            $appointment->save();
            $released++;

            if ($companyData) {
                try {
                    $appointment->load('service');
                    Mail::to($appointment->client_email)->send(
                        new RemoteAssistanceRejected(
                            $appointment,
                            $companyData,
                            'No pudimos verificar tu pago dentro del plazo y el hueco se ha liberado.'
                        )
                    );
                } catch (\Exception $e) {
                    $this->error("No se pudo avisar de la liberación (cita {$appointment->id}): {$e->getMessage()}");
                }
            }

            $this->info("Liberada la cita {$appointment->id} (plazo de verificación agotado).");
        }

        $this->info("Total liberadas: {$released}.");

        return self::SUCCESS;
    }
}
