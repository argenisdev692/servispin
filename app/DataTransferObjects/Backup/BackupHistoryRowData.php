<?php

namespace App\DataTransferObjects\Backup;

final readonly class BackupHistoryRowData
{
    public function __construct(
        public string $id,
        public string $formattedDate,
        public string $filename,
        public string $formattedSize,
        public string $disk,
        public string $status,
        public string $statusBadgeClass,
        public string $createdAgo,
        public string $showUrl,
        public string $downloadUrl,
        public string $destroyUrl,
        public bool $canDownload,
        public bool $canDelete,
        public bool $exists,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'formatted_date' => $this->formattedDate,
            'filename' => $this->filename,
            'formatted_size' => $this->formattedSize,
            'disk' => $this->disk,
            'status' => $this->status,
            'status_badge_class' => $this->statusBadgeClass,
            'created_ago' => $this->createdAgo,
            'show_url' => $this->showUrl,
            'download_url' => $this->downloadUrl,
            'destroy_url' => $this->destroyUrl,
            'can_download' => $this->canDownload,
            'can_delete' => $this->canDelete,
            'exists' => $this->exists,
        ];
    }
}
