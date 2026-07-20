<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;

final class BackupScheduleRegistrar
{
    public function register(Schedule $schedule): void
    {
        $mutexName = (string) config('backup-retention.scheduler_mutex_name', 'spatie-backup-pipeline');
        $mutexExpires = (int) config('backup-retention.scheduler_mutex_expires_minutes', 180);

        $schedule->command('backup:run --only-db')
            ->dailyAt('02:00')
            ->name($mutexName)
            ->createMutexNameUsing($mutexName)
            ->withoutOverlapping($mutexExpires)
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/backup-run.log'));

        $schedule->command('backup:clean')
            ->dailyAt('02:30')
            ->name($mutexName)
            ->createMutexNameUsing($mutexName)
            ->withoutOverlapping($mutexExpires)
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/backup-clean.log'));

        $schedule->command('backup:monitor')
            ->dailyAt('03:00')
            ->name($mutexName)
            ->createMutexNameUsing($mutexName)
            ->withoutOverlapping($mutexExpires)
            ->onOneServer()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/backup-monitor.log'));
    }
}
