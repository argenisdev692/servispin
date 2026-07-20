<?php

namespace App\Listeners\Backup;

use App\Services\Backup\BackupRetentionService;
use Spatie\Backup\Events\CleanupWasSuccessful;

final class RecordAutomaticBackupCleanupListener
{
    public function __construct(
        private readonly BackupRetentionService $backupRetentionService,
    ) {}

    public function handle(CleanupWasSuccessful $event): void
    {
        $this->backupRetentionService->synchronizeFilesystemWithHistory();
        $this->backupRetentionService->anonymizeExpiredAuditRecords();
    }
}
