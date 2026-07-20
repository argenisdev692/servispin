<?php

namespace App\Models\Backup;

use Illuminate\Support\Carbon;

final class BackupFile
{
    public function __construct(
        public readonly string $id,
        public readonly string $disk,
        public readonly string $path,
        public readonly string $filename,
        public readonly int $sizeInBytes,
        public readonly Carbon $createdAt,
        public readonly bool $exists,
    ) {}

    public function getRouteKey(): string
    {
        return $this->id;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function isMissing(): bool
    {
        return ! $this->exists;
    }
}
