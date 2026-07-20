<?php

namespace App\Providers;

use App\Listeners\Backup\RecordAutomaticBackupCleanupListener;
use App\Listeners\Backup\RecordManualBackupDeletionListener;
use App\Listeners\Backup\RecordSuccessfulBackupListener;
use App\Services\Backup\BackupNotificationRecipientResolver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupWasSuccessful;

final class BackupRetentionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->booted(function (): void {
            $this->hydrateBackupNotificationRecipients();
        });

        Event::listen(BackupWasSuccessful::class, RecordSuccessfulBackupListener::class);
        Event::listen(CleanupWasSuccessful::class, RecordAutomaticBackupCleanupListener::class);
        Event::listen('eloquent.created: '.Activity::class, RecordManualBackupDeletionListener::class);
    }

    private function hydrateBackupNotificationRecipients(): void
    {
        try {
            $recipients = $this->app->make(BackupNotificationRecipientResolver::class)->resolve();

            if ($recipients === []) {
                return;
            }

            config([
                'backup.notifications.mail.to' => count($recipients) === 1
                    ? $recipients[0]
                    : $recipients,
            ]);
        } catch (\Throwable) {
            // Durante migrate/install la BD puede no estar lista todavía.
        }
    }
}
