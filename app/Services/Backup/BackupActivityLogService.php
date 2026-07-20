<?php

namespace App\Services\Backup;

use App\Models\Backup\BackupFile;
use App\Models\User;

final class BackupActivityLogService
{
    public function logViewed(User $user, BackupFile $backupFile): void
    {
        activity('backup-history')
            ->causedBy($user)
            ->withProperties([
                'disk' => $backupFile->disk,
                'path' => $backupFile->path,
                'filename' => $backupFile->filename,
                'action' => 'viewed',
            ])
            ->log('Backup details viewed');
    }

    public function logDownloaded(User $user, BackupFile $backupFile): void
    {
        activity('backup-history')
            ->causedBy($user)
            ->withProperties([
                'disk' => $backupFile->disk,
                'path' => $backupFile->path,
                'filename' => $backupFile->filename,
                'action' => 'downloaded',
            ])
            ->log('Backup downloaded');
    }

    public function logDeleted(User $user, BackupFile $backupFile): void
    {
        activity('backup-history')
            ->causedBy($user)
            ->withProperties([
                'disk' => $backupFile->disk,
                'path' => $backupFile->path,
                'filename' => $backupFile->filename,
                'action' => 'deleted',
            ])
            ->log('Backup deleted');
    }

    public function logListed(User $user): void
    {
        activity('backup-history')
            ->causedBy($user)
            ->withProperties([
                'action' => 'listed',
            ])
            ->log('Backup history listed');
    }
}
