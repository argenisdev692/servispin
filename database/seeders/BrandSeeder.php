<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;
use Illuminate\Support\Str; // Import Str for UUID

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'AEG', 'Ariston', 'Balay', 'Bosch', 'Candy', 'Corberó', 'Daewoo Electronics',
            'Edesa', 'Electrolux', 'Fagor', 'General Electric', 'Haier', 'Hyundai',
            'Ignis', 'Indesit', 'LG', 'Miele', 'MundoClima', 'Neff', 'New Pol',
            'Otsein Hoover', 'Panasonic', 'Samsung', 'Sharp', 'Siemens', 'Teka',
            'Ufesa', 'Westinghouse', 'Whirlpool', 'Zanussi',
            'Otro' // Add the "Other" option
        ];

        // Sort alphabetically for the seeder consistency
        sort($brands);

        foreach ($brands as $brandName) {
            // Use create directly, the model's boot method handles UUID
            Brand::create([
                'name' => $brandName,
                // 'logo_path' => null, // Set logo path if you have them
                ]);
        }

        $this->command->info(count($brands) . ' brands seeded.');
    }
}
