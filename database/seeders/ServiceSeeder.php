<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Diagnostico',
                'description' => 'Diagnóstico general para identificar problemas.',
                'duration' => 60,
                'price' => 25.00,
            ],
            [
                'name' => 'Reparación de Lavadora',
                'description' => 'Servicio de diagnóstico y reparación de lavadoras de todas las marcas.',
                'duration' => 60, // 60 minutos
                'price' => 50.00,
            ],
            [
                'name' => 'Reparación de Secadora',
                'description' => 'Servicio de diagnóstico y reparación de secadoras de todas las marcas.',
                'duration' => 60, // 60 minutos
                'price' => 50.00,
            ],
            [
                'name' => 'Reparación de Lavavajillas',
                'description' => 'Servicio de diagnóstico y reparación de lavavajillas de todas las marcas.',
                'duration' => 60, // 60 minutos
                'price' => 50.00,
            ],
            [
                'name' => 'Instalación de Lavadora',
                'description' => 'Servicio de instalación y configuración de lavadoras nuevas.',
                'duration' => 45, // 45 minutos
                'price' => 35.00,
            ],
            [
                'name' => 'Mantenimiento Preventivo',
                'description' => 'Servicio de mantenimiento preventivo para extender la vida útil de sus electrodomésticos.',
                'duration' => 90, // 90 minutos
                'price' => 75.00,
            ],
        ];

        foreach ($services as $service) {
            Service::create([
                'uuid' => Str::uuid(),
                'name' => $service['name'],
                'description' => $service['description'],
                'duration' => $service['duration'],
                'price' => $service['price'],
            ]);
        }
    }
} 