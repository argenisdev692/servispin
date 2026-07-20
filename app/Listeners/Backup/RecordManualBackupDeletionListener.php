<?php

namespace App\Listeners\Backup;

use App\Services\Backup\BackupRetentionService;
use Spatie\Activitylog\Models\Activity;

final class RecordManualBackupDeletionListener
{
    private const string MANUAL_DELETE_DESCRIPTION = 'Backup deleted';

    private const string BACKUP_HISTORY_LOG_NAME = 'backup-history';

    public function __construct(
        private readonly BackupRetentionService $backupRetentionService,
    ) {}

    public function handle(Activity $activity): void
    {
        if ($activity->log_name !== self::BACKUP_HISTORY_LOG_NAME) {
            return;
        }

        if ($activity->description !== self::MANUAL_DELETE_DESCRIPTION) {
            return;
        }

        $properties = $activity->properties?->toArray() ?? [];

        $disk = (string) ($properties['disk'] ?? '');
        $path = (string) ($properties['path'] ?? '');

        if ($disk === '' || $path === '') {
            return;
        }

        $this->backupRetentionService->registerManualDeletion(
            disk: $disk,
            path: $path,
            userId: $activity->causer_id !== null ? (int) $activity->causer_id : null,
        );
    }
}
