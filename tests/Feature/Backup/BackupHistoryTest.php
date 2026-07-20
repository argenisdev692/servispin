<?php

namespace Tests\Feature\Backup;

use App\Models\Backup\BackupFile;
use App\Models\User;
use App\Repositories\Backup\BackupHistoryRepositoryInterface;
use App\Support\Backup\BackupFileIdentifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackupHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $regularUser;

    private BackupFile $backupFile;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'sanctum']);
        $userRole = Role::create(['name' => 'User', 'guard_name' => 'sanctum']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole($userRole);

        Storage::fake('local');

        $path = 'Laravel/2026-07-20-12-00-00.zip';
        Storage::disk('local')->put($path, 'backup-content');

        $this->backupFile = new BackupFile(
            id: BackupFileIdentifier::fromDiskAndPath('local', $path),
            disk: 'local',
            path: $path,
            filename: '2026-07-20-12-00-00.zip',
            sizeInBytes: 14,
            createdAt: Carbon::parse('2026-07-20 12:00:00'),
            exists: true,
        );

        $this->app->bind(BackupHistoryRepositoryInterface::class, FakeBackupHistoryRepository::class);
        FakeBackupHistoryRepository::seed([$this->backupFile]);
    }

    #[Test]
    public function admin_can_view_backup_history_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.backup-history.index'))
            ->assertOk()
            ->assertSee('Historial de Backups');

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Backup history listed',
            'causer_id' => $this->admin->id,
        ]);
    }

    #[Test]
    public function non_admin_cannot_view_backup_history_index(): void
    {
        $this->actingAs($this->regularUser)
            ->get(route('admin.backup-history.index'))
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_fetch_datatable_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.backup-history.datatable', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.filename', '2026-07-20-12-00-00.zip')
            ->assertJsonPath('data.0.status', 'Available');
    }

    #[Test]
    public function datatable_marks_missing_backups(): void
    {
        $missingPath = 'Laravel/missing.zip';
        $missingBackup = new BackupFile(
            id: BackupFileIdentifier::fromDiskAndPath('local', $missingPath),
            disk: 'local',
            path: $missingPath,
            filename: 'missing.zip',
            sizeInBytes: 0,
            createdAt: Carbon::parse('2026-07-19 10:00:00'),
            exists: false,
        );

        FakeBackupHistoryRepository::seed([
            $this->backupFile,
            $missingBackup,
        ]);

        $this->actingAs($this->admin)
            ->getJson(route('admin.backup-history.datatable', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]))
            ->assertOk()
            ->assertJsonFragment([
                'filename' => 'missing.zip',
                'status' => 'Missing',
                'status_badge_class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
            ]);
    }

    #[Test]
    public function admin_can_view_backup_details_and_activity_is_logged(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.backup-history.show', $this->backupFile))
            ->assertOk()
            ->assertSee('2026-07-20-12-00-00.zip');

        $this->assertTrue(
            Activity::query()
                ->where('description', 'Backup details viewed')
                ->where('causer_id', $this->admin->id)
                ->exists()
        );
    }

    #[Test]
    public function admin_can_download_backup_and_activity_is_logged(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.backup-history.download', $this->backupFile));

        $response->assertOk();

        $this->assertTrue(
            Activity::query()
                ->where('description', 'Backup downloaded')
                ->where('causer_id', $this->admin->id)
                ->exists()
        );
    }

    #[Test]
    public function non_admin_cannot_download_backup(): void
    {
        $this->actingAs($this->regularUser)
            ->get(route('admin.backup-history.download', $this->backupFile))
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_delete_backup_and_activity_is_logged(): void
    {
        $this->actingAs($this->admin)
            ->deleteJson(route('admin.backup-history.destroy', $this->backupFile))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Storage::disk('local')->assertMissing($this->backupFile->path);

        $this->assertTrue(
            Activity::query()
                ->where('description', 'Backup deleted')
                ->where('causer_id', $this->admin->id)
                ->exists()
        );
    }

    #[Test]
    public function non_admin_cannot_delete_backup(): void
    {
        $this->actingAs($this->regularUser)
            ->deleteJson(route('admin.backup-history.destroy', $this->backupFile))
            ->assertForbidden();
    }

    #[Test]
    public function download_returns_not_found_when_backup_is_missing(): void
    {
        $missingPath = 'Laravel/not-found.zip';
        $missingBackup = new BackupFile(
            id: BackupFileIdentifier::fromDiskAndPath('local', $missingPath),
            disk: 'local',
            path: $missingPath,
            filename: 'not-found.zip',
            sizeInBytes: 0,
            createdAt: Carbon::parse('2026-07-18 08:00:00'),
            exists: false,
        );

        FakeBackupHistoryRepository::seed([$missingBackup]);

        $this->actingAs($this->admin)
            ->get(route('admin.backup-history.download', $missingBackup))
            ->assertNotFound();
    }
}
