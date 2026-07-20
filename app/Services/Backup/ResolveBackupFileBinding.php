<?php

namespace App\Services\Backup;

use App\Repositories\Backup\BackupHistoryRepositoryInterface;

final class ResolveBackupFileBinding
{
    public function __construct(
        private readonly BackupHistoryRepositoryInterface $repository,
    ) {}

    public function __invoke(string $value): mixed
    {
        $backupFile = $this->repository->findById($value);

        if ($backupFile === null) {
            abort(404);
        }

        return $backupFile;
    }
}
