<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
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
                'price' => 0.00,
            ],
            [
                'name' => 'Reparación de Lavadora',
                'description' => 'Servicio de diagnóstico y reparación de lavadoras de todas las marcas.',
                'duration' => 60, // 60 minutos
                'price' => 0.00,
            ],
            [
                'name' => 'Reparación de Secadora',
                'description' => 'Servicio de diagnóstico y reparación de secadoras de todas las marcas.',
                'duration' => 60, // 60 minutos
                'price' => 0.00,
            ],
            [
                'name' => 'Reparación de Lavavajillas',
                'description' => 'Servicio de diagnóstico y reparación de lavavajillas de todas las marcas.',
                'duration' => 60, // 60 minutos
                'price' => 0.00,
            ],
            [
                'name' => 'Instalación de Lavadora',
                'description' => 'Servicio de instalación y configuración de lavadoras nuevas.',
                'duration' => 45, // 45 minutos
                'price' => 0.00,
            ],
            [
                'name' => 'Mantenimiento Preventivo',
                'description' => 'Servicio de mantenimiento preventivo para extender la vida útil de sus electrodomésticos.',
                'duration' => 90, // 90 minutos
                'price' => 0.00,
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

        // Asistencia remota por videollamada. Precio y duración son provisionales
        // (D-3): Cesar los ajusta desde el CRUD de servicios. El único dato que
        // el módulo remoto necesita de verdad es is_remote = true.
        // updateOrCreate para poder resembrar sin duplicar el servicio.
        Service::updateOrCreate(
            ['name' => 'Asistencia Técnica Remota'],
            [
                'uuid' => Str::uuid(),
                'description' => 'Sesión de asistencia técnica por videollamada. Un técnico te guía '
                    .'paso a paso para diagnosticar o reparar tu electrodoméstico desde donde estés.',
                'duration' => config('remote_assistance.default_duration', 20),
                'price' => config('remote_assistance.default_price', 35.00),
                'active' => true,
                'is_remote' => true,
            ]
        );
    }
}
