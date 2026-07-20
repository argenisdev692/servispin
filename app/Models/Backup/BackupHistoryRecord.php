<?php

namespace App\Models\Backup;

use App\Enums\Backup\BackupDeletionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BackupHistoryRecord extends Model
{
    /** @use HasFactory<\Database\Factories\Backup\BackupHistoryRecordFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'external_id',
        'disk',
        'path',
        'filename',
        'size_bytes',
        'backup_created_at',
        'file_deleted_at',
        'deletion_type',
        'deleted_by_user_id',
        'audit_retention_expires_at',
        'anonymized_at',
    ];

    protected function casts(): array
    {
        return [
            'backup_created_at' => 'datetime',
            'file_deleted_at' => 'datetime',
            'audit_retention_expires_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'deletion_type' => BackupDeletionType::class,
            'size_bytes' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(static function (BackupHistoryRecord $record): void {
            if ($record->uuid === null) {
                $record->uuid = (string) Str::uuid();
            }
        });
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by_user_id');
    }

    public function isFilePresent(): bool
    {
        return $this->file_deleted_at === null;
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }
}
