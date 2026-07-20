<?php

namespace App\Notifications\Backup;

use App\Services\Backup\BackupNotificationRecipientResolver;
use Illuminate\Support\Facades\Log;
use Spatie\Backup\Notifications\Notifiable as SpatieBackupNotifiable;

final class AdminBackupNotifiable extends SpatieBackupNotifiable
{
    /**
     * @return string|array<int, string>
     */
    public function routeNotificationForMail(): string|array
    {
        $resolver = app(BackupNotificationRecipientResolver::class);
        $recipients = $resolver->resolve();

        if ($recipients === []) {
            Log::warning('Backup notification skipped: no admin recipients found with manage admin permission.');

            $fallback = (string) config('mail.from.address');

            return $fallback !== '' ? $fallback : 'noreply@localhost';
        }

        if (count($recipients) === 1) {
            return $recipients[0];
        }

        return $recipients;
    }
}
