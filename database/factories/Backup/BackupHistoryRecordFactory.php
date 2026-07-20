<?php

namespace Database\Factories\Backup;

use App\Enums\Backup\BackupDeletionType;
use App\Models\Backup\BackupHistoryRecord;
use App\Support\Backup\BackupFileIdentifier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<BackupHistoryRecord>
 */
class BackupHistoryRecordFactory extends Factory
{
    protected $model = BackupHistoryRecord::class;

    public function definition(): array
    {
        $disk = 'local';
        $path = 'Laravel/'.fake()->uuid().'.zip';
        $backupCreatedAt = Carbon::parse(fake()->dateTimeBetween('-30 days', 'now'));

        return [
            'uuid' => (string) Str::uuid(),
            'external_id' => BackupFileIdentifier::fromDiskAndPath($disk, $path),
            'disk' => $disk,
            'path' => $path,
            'filename' => basename($path),
            'size_bytes' => fake()->numberBetween(1024, 10485760),
            'backup_created_at' => $backupCreatedAt,
            'file_deleted_at' => null,
            'deletion_type' => null,
            'deleted_by_user_id' => null,
            'audit_retention_expires_at' => $backupCreatedAt->copy()->addDays((int) config('backup-retention.audit_retention_days', 365)),
            'anonymized_at' => null,
        ];
    }

    public function deletedAutomatically(): static
    {
        return $this->state(static function (array $attributes): array {
            return [
                'file_deleted_at' => now(),
                'deletion_type' => BackupDeletionType::Automatic,
                'deleted_by_user_id' => null,
            ];
        });
    }

    public function deletedManually(int $userId): static
    {
        return $this->state(static function (array $attributes) use ($userId): array {
            return [
                'file_deleted_at' => now(),
                'deletion_type' => BackupDeletionType::Manual,
                'deleted_by_user_id' => $userId,
            ];
        });
    }
}
