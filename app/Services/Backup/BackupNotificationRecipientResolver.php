<?php

namespace App\Services\Backup;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BackupNotificationRecipientResolver
{
    private const string ADMIN_PERMISSION = 'manage admin';

    /**
     * Guard usado en roles/permisos del proyecto (DatabaseSeeder, policies, @can).
     */
    private const string PERMISSION_GUARD = 'sanctum';

    /**
     * @return array<int, string>
     */
    public function resolve(): array
    {
        return User::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function (Builder $query): void {
                $query
                    ->whereHas('permissions', function (Builder $permissionQuery): void {
                        $permissionQuery
                            ->where('name', self::ADMIN_PERMISSION)
                            ->where('guard_name', self::PERMISSION_GUARD);
                    })
                    ->orWhereHas('roles', function (Builder $roleQuery): void {
                        $roleQuery
                            ->where('guard_name', self::PERMISSION_GUARD)
                            ->whereHas('permissions', function (Builder $permissionQuery): void {
                                $permissionQuery
                                    ->where('name', self::ADMIN_PERMISSION)
                                    ->where('guard_name', self::PERMISSION_GUARD);
                            });
                    });
            })
            ->orderBy('id')
            ->pluck('email')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, string>
     */
    public function resolveCollection(): Collection
    {
        return collect($this->resolve());
    }
}
