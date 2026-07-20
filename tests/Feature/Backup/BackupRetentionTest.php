<?php

namespace Tests\Feature\Backup;

use App\Enums\Backup\BackupDeletionType;
use App\Listeners\Backup\RecordAutomaticBackupCleanupListener;
use App\Listeners\Backup\RecordManualBackupDeletionListener;
use App\Listeners\Backup\RecordSuccessfulBackupListener;
use App\Models\Backup\BackupFile;
use App\Models\Backup\BackupHistoryRecord;
use App\Models\User;
use App\Repositories\Backup\BackupHistoryRepositoryInterface;
use App\Services\Backup\BackupRetentionService;
use App\Support\Backup\BackupFileIdentifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackupRetentionTest extends TestCase
{
    use RefreshDatabase;

    private BackupRetentionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->app->bind(BackupHistoryRepositoryInterface::class, FakeBackupHistoryRepository::class);
        FakeBackupHistoryRepository::seed([]);

        $this->service = app(BackupRetentionService::class);
    }

    #[Test]
    public function la_configuracion_oficial_mantiene_backups_durante_noventa_dias(): void
    {
        $this->assertSame(90, (int) config('backup.cleanup.default_strategy.keep_all_backups_for_days'));
        $this->assertSame(0, (int) config('backup.cleanup.default_strategy.keep_daily_backups_for_days'));
        $this->assertSame(0, (int) config('backup.cleanup.default_strategy.keep_weekly_backups_for_weeks'));
        $this->assertSame(0, (int) config('backup.cleanup.default_strategy.keep_monthly_backups_for_months'));
        $this->assertSame(0, (int) config('backup.cleanup.default_strategy.keep_yearly_backups_for_years'));
    }

    #[Test]
    public function registra_un_backup_exitoso_en_el_historial(): void
    {
        $backupName = (string) config('backup.backup.name');
        $path = $backupName.'/2026-07-20-02-00-00.zip';

        Storage::disk('local')->put($path, 'zip-content');

        $listener = app(RecordSuccessfulBackupListener::class);
        $listener->handle(new BackupWasSuccessful('local', $backupName));

        $this->assertDatabaseHas('backup_history_records', [
            'disk' => 'local',
            'path' => $path,
            'filename' => '2026-07-20-02-00-00.zip',
            'file_deleted_at' => null,
        ]);
    }

    #[Test]
    public function marca_eliminacion_automatica_cuando_el_archivo_ya_no_existe(): void
    {
        $disk = 'local';
        $path = 'Laravel/auto-deleted.zip';
        $externalId = BackupFileIdentifier::fromDiskAndPath($disk, $path);

        BackupHistoryRecord::factory()->create([
            'external_id' => $externalId,
            'disk' => $disk,
            'path' => $path,
            'filename' => 'auto-deleted.zip',
            'backup_created_at' => Carbon::now()->subDays(91),
        ]);

        $listener = app(RecordAutomaticBackupCleanupListener::class);
        $listener->handle(new CleanupWasSuccessful('local', (string) config('backup.backup.name')));

        $record = BackupHistoryRecord::query()->where('external_id', $externalId)->firstOrFail();

        $this->assertNotNull($record->file_deleted_at);
        $this->assertSame(BackupDeletionType::Automatic, $record->deletion_type);
        $this->assertNull($record->deleted_by_user_id);
    }

    #[Test]
    public function registra_quien_elimino_manualmente_desde_activity_log(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'sanctum']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $disk = 'local';
        $path = 'Laravel/manual-delete.zip';

        BackupHistoryRecord::factory()->create([
            'external_id' => BackupFileIdentifier::fromDiskAndPath($disk, $path),
            'disk' => $disk,
            'path' => $path,
            'filename' => 'manual-delete.zip',
        ]);

        $activity = Activity::query()->create([
            'log_name' => 'backup-history',
            'description' => 'Backup deleted',
            'causer_type' => User::class,
            'causer_id' => $admin->id,
            'properties' => [
                'disk' => $disk,
                'path' => $path,
                'filename' => 'manual-delete.zip',
                'action' => 'deleted',
            ],
        ]);

        $listener = app(RecordManualBackupDeletionListener::class);
        $listener->handle($activity);

        $this->assertDatabaseHas('backup_history_records', [
            'path' => $path,
            'deletion_type' => BackupDeletionType::Manual->value,
            'deleted_by_user_id' => $admin->id,
        ]);
    }

    #[Test]
    public function anonimiza_registros_de_auditoria_caducados_para_gdpr(): void
    {
        $admin = User::factory()->create();

        $record = BackupHistoryRecord::factory()->deletedManually($admin->id)->create([
            'audit_retention_expires_at' => now()->subDay(),
            'anonymized_at' => null,
        ]);

        $anonymized = $this->service->anonymizeExpiredAuditRecords();

        $record->refresh();

        $this->assertSame(1, $anonymized);
        $this->assertNull($record->deleted_by_user_id);
        $this->assertNotNull($record->anonymized_at);
    }

    #[Test]
    public function el_comando_de_sincronizacion_actualiza_el_historial(): void
    {
        $disk = 'local';
        $path = 'Laravel/2026-07-20-03-00-00.zip';
        Storage::disk('local')->put($path, 'content');

        FakeBackupHistoryRepository::seed([
            new BackupFile(
                id: BackupFileIdentifier::fromDiskAndPath($disk, $path),
                disk: $disk,
                path: $path,
                filename: '2026-07-20-03-00-00.zip',
                sizeInBytes: 7,
                createdAt: Carbon::parse('2026-07-20 03:00:00'),
                exists: true,
            ),
        ]);

        Artisan::call('backup:retention-sync');

        $this->assertDatabaseHas('backup_history_records', [
            'disk' => $disk,
            'path' => $path,
        ]);
    }

    #[Test]
    public function el_scheduler_incluye_los_comandos_de_backup(): void
    {
        Artisan::call('schedule:list');

        $output = Artisan::output();

        $this->assertStringContainsString('backup:run', $output);
        $this->assertStringContainsString('backup:clean', $output);
        $this->assertStringContainsString('backup:monitor', $output);
        $this->assertStringContainsString('02:00', $output);
        $this->assertStringContainsString('02:30', $output);
        $this->assertStringContainsString('03:00', $output);
    }
}
