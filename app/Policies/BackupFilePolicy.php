<?php

namespace App\Policies;

use App\Models\Backup\BackupFile;
use App\Models\User;

final class BackupFilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin', 'sanctum');
    }

    public function view(User $user, BackupFile $backupFile): bool
    {
        return $user->hasRole('Admin', 'sanctum');
    }

    public function download(User $user, BackupFile $backupFile): bool
    {
        return $user->hasRole('Admin', 'sanctum');
    }

    public function delete(User $user, BackupFile $backupFile): bool
    {
        return $user->hasRole('Admin', 'sanctum');
    }
}
