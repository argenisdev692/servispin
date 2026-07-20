<?php

namespace App\Enums\Backup;

enum BackupDeletionType: string
{
    case Automatic = 'automatic';
    case Manual = 'manual';
}
