<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
// use App\Models\MainCategories; // Commented out if not used
use App\Models\Category;
// use App\Models\StatuOptions; // Commented out if not used
use App\Models\CompanyData; // Import CompanyData
use Ramsey\Uuid\Uuid; // Import Uuid
use App\Models\Brand;

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
        // $adminRole->syncPermissions($permissions); // Assign permissions later if needed per role

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
        // $userRole->syncPermissions([$permissions[1]]); // Assign permissions later if needed per role

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
        // $othersRole->syncPermissions([$permissions[2]]); // Assign permissions later if needed per role
        // END OTHERS ROLES

        // Assign all permissions to Admin for simplicity in this example
        // You might want more granular control later
        $allPermissionNames = collect($permissions)->pluck('name');
        $adminRole->syncPermissions($allPermissionNames);

        // Crear la categoría "General" para blog
       Category::create([
            'category_name' => 'Blog',
            'description' => 'Valor por defecto',
            'image' => 'Valor por defecto',
            'user_id' => $adminUser->id, // Use the created admin user's ID
        ]);
        // end

        // COMPANY DATA
        CompanyData::create([
            'uuid' => Uuid::uuid4()->toString(),
            'name' => 'Cesar Gonzalez', // As requested
            'company_name' => 'ServiSpin', // As requested
            'signature_path' => null, // As requested (nullable string)
            'email' => 'info@servispin.net', // Example email
            'phone' => '+34643940970', // Example phone
            'address' => 'C. Delicias, 42, 35110 Vecindario, Las Palmas, España', // Updated Address
            'website' => 'https://servispin.net', // Example website
            'social_media_facebook' => 'https://facebook.com/servispin', // Example FB
            'social_media_instagram' => 'https://instagram.com/servispin', // Example IG
            'social_media_twitter' => null, // Example Twitter (or null)
            'address_google_map' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3527.4891578227844!2d-15.446540325810773!3d27.856240418910957!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!2sServiSpin%20Vecindario!5e0!3m2!1ses!2spt!4v1708740894900!5m2!1ses!2spt', // Added Google Map Embed URL
            'user_id' => $adminUser->id, // Assign to the admin user created above
            'latitude' => 27.856236, // Updated Latitude from Google Maps link
            'longitude' => -15.443965, // Updated Longitude from Google Maps link
        ]);
        // END COMPANY DATA

        // Crear reglas de disponibilidad
        $this->command->info('Seeding availability rules...');
        // Lunes a viernes: 9:00 - 18:00
        for ($day = 1; $day <= 5; $day++) {
            \App\Models\AvailabilityRule::create([
                'day_of_week' => $day,
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ]);
        }

        // Sábado: 10:00 - 14:00
        \App\Models\AvailabilityRule::create([
            'day_of_week' => 6,
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
            'is_available' => true,
        ]);

        // Domingo: No disponible
        \App\Models\AvailabilityRule::create([
            'day_of_week' => 0,
            'start_time' => '00:00:00',
            'end_time' => '00:00:00',
            'is_available' => false,
        ]);

        $this->call([
            ServiceSeeder::class,
            AvailabilityRuleSeeder::class,
            BrandSeeder::class,
            CanaryIslandHolidaysSeeder::class,
        ]);
    }
}