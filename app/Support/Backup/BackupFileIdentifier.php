<?php

namespace App\Support\Backup;

final class BackupFileIdentifier
{
    public static function fromDiskAndPath(string $disk, string $path): string
    {
        $payload = $disk.'|'.$path;
        $encoded = base64_encode(hash('sha256', $payload, true));

        return rtrim(strtr($encoded, '+/', '-_'), '=');
    }
}
