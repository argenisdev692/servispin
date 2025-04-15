<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AvailabilityRule;

class AvailabilityRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Días de la semana: 0 (domingo) a 6 (sábado)
        $rules = [
            // Lunes (1)
            [
                'day_of_week' => 1,
                'start_time' => '08:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ],
            // Martes (2)
            [
                'day_of_week' => 2,
                'start_time' => '08:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ],
            // Miércoles (3)
            [
                'day_of_week' => 3,
                'start_time' => '08:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ],
            // Jueves (4)
            [
                'day_of_week' => 4,
                'start_time' => '08:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ],
            // Viernes (5)
            [
                'day_of_week' => 5,
                'start_time' => '08:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ],
            // Sábado (6)
            [
                'day_of_week' => 6,
                'start_time' => '08:00:00',
                'end_time' => '16:30:00',
                'is_available' => true,
            ],
            // Domingo (0)
            [
                'day_of_week' => 0,
                'start_time' => '08:00:00',
                'end_time' => '14:30:00',
                'is_available' => true,
            ],
        ];

        foreach ($rules as $rule) {
            AvailabilityRule::updateOrCreate(
                ['day_of_week' => $rule['day_of_week']],
                [
                    'start_time' => $rule['start_time'],
                    'end_time' => $rule['end_time'],
                    'is_available' => $rule['is_available'],
                ]
            );
        }
    }
} 