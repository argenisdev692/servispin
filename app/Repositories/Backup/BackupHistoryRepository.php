<?php

namespace App\Repositories\Backup;

use App\Models\Backup\BackupFile;
use App\Support\Backup\BackupFileIdentifier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;

final class BackupHistoryRepository implements BackupHistoryRepositoryInterface
{
    /**
     * @return Collection<int, BackupFile>
     */
    public function all(): Collection
    {
        $backupName = (string) config('backup.backup.name');
        $disks = (array) config('backup.backup.destination.disks', ['local']);

        $backups = collect();

        foreach ($disks as $disk) {
            $destination = BackupDestination::create((string) $disk, $backupName);

            if (! $destination->isReachable()) {
                continue;
            }

            foreach ($destination->backups() as $backup) {
                $backups->push($this->mapBackup($backup, (string) $disk));
            }
        }

        return $backups
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

        return Storage::disk($backupFile->disk)->delete($backupFile->path);
    }

    public function exists(BackupFile $backupFile): bool
    {
        return Storage::disk($backupFile->disk)->exists($backupFile->path);
    }

    private function mapBackup(Backup $backup, string $disk): BackupFile
    {
        $path = $backup->path();
        $createdAt = Carbon::parse($backup->date());
        $exists = Storage::disk($disk)->exists($path);

        return new BackupFile(
            id: BackupFileIdentifier::fromDiskAndPath($disk, $path),
            disk: $disk,
            path: $path,
            filename: basename($path),
            sizeInBytes: (int) $backup->sizeInBytes(),
            createdAt: $createdAt,
            exists: $exists,
        );
    }
}
