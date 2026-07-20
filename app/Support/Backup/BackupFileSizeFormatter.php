<?php

namespace App\Support\Backup;

final class BackupFileSizeFormatter
{
    public static function format(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 2).' KB';
        }

        if ($bytes < 1073741824) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        return number_format($bytes / 1073741824, 2).' GB';
    }
}
