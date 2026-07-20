<?php

namespace App\Listeners\Backup;

use App\Services\Backup\BackupRetentionService;
use Spatie\Backup\Events\BackupWasSuccessful;

final class RecordSuccessfulBackupListener
{
    public function __construct(
        private readonly BackupRetentionService $backupRetentionService,
    ) {}

    public function handle(BackupWasSuccessful $event): void
    {
        $this->backupRetentionService->registerFromSuccessfulBackup($event);
    }
}
