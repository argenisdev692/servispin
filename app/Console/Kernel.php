<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Enviar recordatorios de citas diariamente a las 9:00 AM
        $schedule->command('appointments:send-reminders')
            ->dailyAt('09:00')
            ->appendOutputTo(storage_path('logs/appointment-reminders.log'));

        // Recordatorio de 30 min de las citas remotas (US-3). Cada 5 min.
        // ⚠️ Solo funciona si el cron del servidor corre de verdad (R-3, T037).
        // withoutOverlapping evita que dos ejecuciones se pisen si una se alarga.
        $schedule->command('appointments:send-imminent-reminders')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/imminent-reminders.log'));

        // Liberar huecos de solicitudes remotas no verificadas a tiempo (FR-12).
        // Cada hora es suficiente: el plazo se mide en horas, no en minutos.
        $schedule->command('appointments:release-unverified')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/release-unverified.log'));

        app(BackupScheduleRegistrar::class)->register($schedule);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
