<?php

namespace Tests\Feature\Backup;

use App\Models\User;
use App\Notifications\Backup\AdminBackupNotifiable;
use App\Services\Backup\BackupNotificationRecipientResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackupNotificationRecipientResolverTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function resuelve_emails_de_usuarios_con_permiso_manage_admin(): void
    {
        $permission = Permission::create([
            'name' => 'manage admin',
            'guard_name' => 'sanctum',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'guard_name' => 'sanctum',
        ]);
        $adminRole->givePermissionTo($permission);

        $admin = User::factory()->create([
            'email' => 'admin-principal@test.local',
        ]);
        $admin->assignRole($adminRole);

        $secondAdmin = User::factory()->create([
            'email' => 'admin-secundario@test.local',
        ]);
        $secondAdmin->assignRole($adminRole);

        User::factory()->create([
            'email' => 'usuario-normal@test.local',
        ]);

        $emails = app(BackupNotificationRecipientResolver::class)->resolve();

        $this->assertSame([
            'admin-principal@test.local',
            'admin-secundario@test.local',
        ], $emails);
    }

    #[Test]
    public function el_notifiable_de_backup_usa_los_emails_de_la_base_de_datos(): void
    {
        $permission = Permission::create([
            'name' => 'manage admin',
            'guard_name' => 'sanctum',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'guard_name' => 'sanctum',
        ]);
        $adminRole->givePermissionTo($permission);

        $admin = User::factory()->create([
            'email' => 'admin-desde-bd@test.local',
        ]);
        $admin->assignRole($adminRole);

        $notifiable = new AdminBackupNotifiable;

        $this->assertSame('admin-desde-bd@test.local', $notifiable->routeNotificationForMail());
    }

    #[Test]
    public function no_falla_cuando_el_guard_por_defecto_es_web(): void
    {
        $this->assertSame('web', (string) config('auth.defaults.guard'));

        $permission = Permission::create([
            'name' => 'manage admin',
            'guard_name' => 'sanctum',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'guard_name' => 'sanctum',
        ]);
        $adminRole->givePermissionTo($permission);

        $admin = User::factory()->create([
            'email' => 'admin-cli@test.local',
        ]);
        $admin->assignRole($adminRole);

        $emails = app(BackupNotificationRecipientResolver::class)->resolve();

        $this->assertSame(['admin-cli@test.local'], $emails);
    }
}
