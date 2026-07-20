<?php

namespace Tests\Feature\Backup;

use App\Models\Backup\BackupFile;
use App\Repositories\Backup\BackupHistoryRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

final class FakeBackupHistoryRepository implements BackupHistoryRepositoryInterface
{
    /** @var Collection<int, BackupFile> */
    private static Collection $backups;

    /**
     * @param  array<int, BackupFile>  $backups
     */
    public static function seed(array $backups): void
    {
        self::$backups = collect($backups);
    }

    public function all(): Collection
    {
        if (! isset(self::$backups)) {
            self::$backups = collect();
        }

        return self::$backups
            ->map(function (BackupFile $backupFile): BackupFile {
                $exists = Storage::disk($backupFile->disk)->exists($backupFile->path);

                return new BackupFile(
                    id: $backupFile->id,
                    disk: $backupFile->disk,
                    path: $backupFile->path,
                    filename: $backupFile->filename,
                    sizeInBytes: $backupFile->sizeInBytes,
                    createdAt: $backupFile->createdAt,
                    exists: $exists,
                );
            })
            ->sortByDesc(static fn (BackupFile $backupFile): int => $backupFile->createdAt->getTimestamp())
            ->values();
    }

    public function findById(string $id): ?BackupFile
    {
        return $this->all()->first(static fn (BackupFile $backupFile): bool => $backupFile->id === $id);
    }

    public function delete(BackupFile $backupFile): bool
    {
        if (! $this->exists($backupFile)) {
            return false;
        }

        $deleted = Storage::disk($backupFile->disk)->delete($backupFile->path);

        if ($deleted) {
            self::$backups = self::$backups
                ->reject(static fn (BackupFile $item): bool => $item->id === $backupFile->id)
                ->values();
        }

        return $deleted;
    }

    public function exists(BackupFile $backupFile): bool
    {
        return Storage::disk($backupFile->disk)->exists($backupFile->path);
    }
}
