<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\MainCategories;
use App\Models\Category;
use App\Models\StatuOptions;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = [
            Permission::create(['name' => 'manage admin', 'guard_name' => 'sanctum']),
            Permission::create(['name' => 'manage user', 'guard_name' => 'sanctum']),
            Permission::create(['name' => 'manage others', 'guard_name' => 'sanctum']),
        ];

        // MANAGER ADMIN
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'sanctum']);
        $adminRole->syncPermissions($permissions);

        $adminUser = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'username' => 'admin01',
            'password' => bcrypt('sistema123')
        ]);
        $adminUser->assignRole($adminRole);
        // END MANAGER ADMIN

        // MANAGER USER
        $userRole = Role::create(['name' => 'User', 'guard_name' => 'sanctum']);
        $userRole->syncPermissions([$permissions[1]]);

        $userUser = User::factory()->create([
            'name' => 'User',
            'email' => 'user@user.com',
            'username' => 'user01',
            'password' => bcrypt('password123')
        ]);
        $userUser->assignRole($userRole);
        // END MANAGER USER

        // OTHERS ROLES
        $othersRole = Role::create(['name' => 'Others', 'guard_name' => 'sanctum']);
        $othersRole->syncPermissions([$permissions[2]]);
        // END OTHERS ROLES

    // Crear la categoría "General" para blog
       Category::create([
            'category_name' => 'Blog',
            'description' => 'Valor por defecto',
            'image' => 'Valor por defecto',
            'user_id' => 1,
        ]);
    // end        

    }
}