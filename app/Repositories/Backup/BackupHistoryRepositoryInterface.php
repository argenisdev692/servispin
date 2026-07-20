<?php

namespace App\Repositories\Backup;

use App\Models\Backup\BackupFile;
use Illuminate\Support\Collection;

interface BackupHistoryRepositoryInterface
{
    /**
     * @return Collection<int, BackupFile>
     */
    public function all(): Collection;

    public function findById(string $id): ?BackupFile;

    public function delete(BackupFile $backupFile): bool;

    public function exists(BackupFile $backupFile): bool;
}
