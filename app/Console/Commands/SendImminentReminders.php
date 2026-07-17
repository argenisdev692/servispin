<?php

namespace App\Console\Commands;

use App\Mail\RemoteAssistanceReminder;
use App\Models\Appointment;
use App\Models\CompanyData;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Recordatorio de 30 minutos antes, solo para citas remotas (US-3, T039).
 *
 * Se programa cada 5 minutos. La clave es la **idempotencia**: la misma cita cae
 * en varias ejecuciones dentro de su ventana, así que se marca
 * `imminent_reminder_sent_at` y no se reenvía. Sin eso, el cliente recibiría el
 * mismo aviso 6 veces.
 *
 * ⚠️ Depende de que el cron del servidor corra de verdad cada 5 min (R-3). Si el
 * cron no está activo en producción, este recordatorio simplemente no llega — y
 * eso hay que verificarlo antes de prometérselo al cliente (T037).
 */
class SendImminentReminders extends Command
{
    protected $signature = 'appointments:send-imminent-reminders';

    protected $description = 'Envía el recordatorio de 30 min de las citas remotas confirmadas';

    public function handle(): int
    {
        $companyData = CompanyData::first();
        if (! $companyData) {
            $this->error('No company data found');

            return self::FAILURE;
        }

        $now = Carbon::now();

        // Ventana: citas que empiezan entre ahora y dentro de 35 min. El margen
        // (30 + holgura) cubre el hueco entre ejecuciones del cron de 5 min sin
        // dejar escapar ninguna. La marca de "ya enviado" evita duplicados.
        $windowEnd = $now->copy()->addMinutes(35);

        $appointments = Appointment::remote()
            ->where('status', Appointment::STATUS_CONFIRMED)
            ->whereNull('imminent_reminder_sent_at')
            ->whereBetween('start_time', [$now, $windowEnd])
            ->get();

        $this->info("Found {$appointments->count()} imminent remote appointments.");

        foreach ($appointments as $appointment) {
            try {
                Mail::to($appointment->client_email)
                    ->send(new RemoteAssistanceReminder($appointment, $companyData, RemoteAssistanceReminder::WHEN_IMMINENT));

                if ($companyData->email) {
                    Mail::to($companyData->email)
                        ->send(new RemoteAssistanceReminder($appointment, $companyData, RemoteAssistanceReminder::WHEN_IMMINENT, true));
                }

                // Se marca DESPUÉS de enviar: si el envío falla, se reintenta en la
                // siguiente pasada en vez de quedarse marcada sin haber avisado.
                $appointment->forceFill(['imminent_reminder_sent_at' => now()])->save();

                $this->info("Sent imminent reminder for appointment ID: {$appointment->id}");
            } catch (\Exception $e) {
                $this->error("Failed imminent reminder for appointment ID: {$appointment->id} - {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
