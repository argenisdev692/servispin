<?php

namespace App\Services\Backup;

use App\Enums\Backup\BackupDeletionType;
use App\Models\Backup\BackupHistoryRecord;
use App\Repositories\Backup\BackupHistoryRepositoryInterface;
use App\Support\Backup\BackupFileIdentifier;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\BackupWasSuccessful;

final class BackupRetentionService
{
    public function __construct(
        private readonly BackupHistoryRepositoryInterface $backupHistoryRepository,
    ) {}

    public function registerFromSuccessfulBackup(BackupWasSuccessful $event): void
    {
        $destination = BackupDestination::create($event->diskName, $event->backupName);
        $newestBackup = $destination->newestBackup();

        if ($newestBackup === null) {
            return;
        }

        $this->registerBackupFile(
            disk: $event->diskName,
            path: $newestBackup->path(),
            filename: basename($newestBackup->path()),
            sizeInBytes: (int) $newestBackup->sizeInBytes(),
            backupCreatedAt: $newestBackup->date(),
        );
    }

    public function registerBackupFile(
        string $disk,
        string $path,
        string $filename,
        int $sizeInBytes,
        CarbonInterface $backupCreatedAt,
    ): BackupHistoryRecord {
        $backupCreatedAt = Carbon::instance($backupCreatedAt);
        $externalId = BackupFileIdentifier::fromDiskAndPath($disk, $path);
        $auditRetentionDays = (int) config('backup-retention.audit_retention_days', 365);

        return BackupHistoryRecord::query()->updateOrCreate(
            ['external_id' => $externalId],
            [
                'disk' => $disk,
                'path' => $path,
                'filename' => $filename,
                'size_bytes' => $sizeInBytes,
                'backup_created_at' => $backupCreatedAt,
                'audit_retention_expires_at' => $backupCreatedAt->copy()->addDays($auditRetentionDays),
                'file_deleted_at' => null,
                'deletion_type' => null,
                'deleted_by_user_id' => null,
                'anonymized_at' => null,
            ],
        );
    }

    public function synchronizeFilesystemWithHistory(): int
    {
        $markedAsDeleted = 0;

        BackupHistoryRecord::query()
            ->whereNull('file_deleted_at')
            ->orderBy('id')
            ->each(function (BackupHistoryRecord $record) use (&$markedAsDeleted): void {
                if (Storage::disk($record->disk)->exists($record->path)) {
                    return;
                }

                $this->markAsAutomaticallyDeleted($record);
                $markedAsDeleted++;
            });

        $this->registerMissingFilesystemBackups();

        return $markedAsDeleted;
    }

    public function registerManualDeletion(string $disk, string $path, ?int $userId): ?BackupHistoryRecord
    {
        $externalId = BackupFileIdentifier::fromDiskAndPath($disk, $path);

        $record = BackupHistoryRecord::query()
            ->where('external_id', $externalId)
            ->first();

        if ($record === null) {
            $record = $this->registerBackupFile(
                disk: $disk,
                path: $path,
                filename: basename($path),
                sizeInBytes: 0,
                backupCreatedAt: Carbon::now(),
            );
        }

        if ($record->file_deleted_at !== null && $record->deletion_type === BackupDeletionType::Manual) {
            return $record;
        }

        $record->file_deleted_at = now();
        $record->deletion_type = BackupDeletionType::Manual;
        $record->deleted_by_user_id = $userId;
        $record->save();

        return $record;
    }

    public function anonymizeExpiredAuditRecords(): int
    {
        $anonymized = 0;

        BackupHistoryRecord::query()
            ->whereNotNull('file_deleted_at')
            ->whereNull('anonymized_at')
            ->where('audit_retention_expires_at', '<=', now())
            ->orderBy('id')
            ->each(function (BackupHistoryRecord $record) use (&$anonymized): void {
                $record->deleted_by_user_id = null;
                $record->anonymized_at = now();
                $record->save();
                $anonymized++;
            });

        return $anonymized;
    }

    private function markAsAutomaticallyDeleted(BackupHistoryRecord $record): void
    {
        $record->file_deleted_at = now();
        $record->deletion_type = BackupDeletionType::Automatic;
        $record->deleted_by_user_id = null;
        $record->save();
    }

    private function registerMissingFilesystemBackups(): void
    {
        foreach ($this->backupHistoryRepository->all() as $backupFile) {
            $externalId = BackupFileIdentifier::fromDiskAndPath($backupFile->disk, $backupFile->path);

            $exists = BackupHistoryRecord::query()
                ->where('external_id', $externalId)
                ->exists();

            if ($exists) {
                continue;
            }

            $this->registerBackupFile(
                disk: $backupFile->disk,
                path: $backupFile->path,
                filename: $backupFile->filename,
                sizeInBytes: $backupFile->sizeInBytes,
                backupCreatedAt: $backupFile->createdAt,
            );
        }
    }
}
