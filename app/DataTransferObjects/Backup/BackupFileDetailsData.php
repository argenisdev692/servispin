<?php

namespace App\DataTransferObjects\Backup;

final readonly class BackupFileDetailsData
{
    public function __construct(
        public string $id,
        public string $filename,
        public string $disk,
        public string $path,
        public string $formattedSize,
        public string $formattedDate,
        public string $createdAgo,
        public string $status,
        public string $statusBadgeClass,
        public bool $exists,
    ) {}
}
