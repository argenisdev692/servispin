<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupRetentionService;
use Illuminate\Console\Command;

final class BackupRetentionSyncCommand extends Command
{
    protected $signature = 'backup:retention-sync {--anonymize : Anonymize audit records past retention}';

    protected $description = 'Synchronize backup history records with the filesystem and optional GDPR anonymization';

    public function __construct(
        private readonly BackupRetentionService $backupRetentionService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $marked = $this->backupRetentionService->synchronizeFilesystemWithHistory();

        $this->info("Marked {$marked} backup(s) as automatically deleted.");

        if ($this->option('anonymize')) {
            $anonymized = $this->backupRetentionService->anonymizeExpiredAuditRecords();
            $this->info("Anonymized {$anonymized} expired audit record(s).");
        }

        return self::SUCCESS;
    }
}
