<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\AvailabilityException;

class CanaryIslandHolidaysSeeder extends Seeder
{
    /**
     * Run the database seeds for Canary Islands holidays.
     * Creates availability exceptions for each holiday.
     *
     * @return void
     */
    public function run()
    {
        $year = now()->year; // Usar el año actual
        
        $holidays = [
            // --- Festivos Nacionales y Autonómicos (Aplicables en todas las islas) ---
            [
                'date' => Carbon::create($year, 1, 1),
                'name' => 'Año Nuevo / New Year\'s Day',
                'scope' => 'Nacional'
            ],
            [
                'date' => Carbon::create($year, 1, 6),
                'name' => 'Epifanía del Señor / Día de Reyes',
                'scope' => 'Nacional'
            ],
            // Semana Santa - fechas variables según el año
            [
                'date' => $this->getEasterThursday($year),
                'name' => 'Jueves Santo / Maundy Thursday',
                'scope' => 'Autonómico Canarias'
            ],
            [
                'date' => $this->getEasterFriday($year),
                'name' => 'Viernes Santo / Good Friday',
                'scope' => 'Nacional'
            ],
            [
                'date' => Carbon::create($year, 5, 1),
                'name' => 'Fiesta del Trabajo / Labour Day',
                'scope' => 'Nacional'
            ],
            [
                'date' => Carbon::create($year, 5, 30),
                'name' => 'Día de Canarias / Canary Islands Day',
                'scope' => 'Autonómico Canarias'
            ],
            [
                'date' => Carbon::create($year, 8, 15),
                'name' => 'Asunción de la Virgen / Assumption Day',
                'scope' => 'Nacional'
            ],
            [
                'date' => Carbon::create($year, 10, 12),
                'name' => 'Fiesta Nacional de España / National Day of Spain',
                'scope' => 'Nacional'
            ],
            [
                'date' => Carbon::create($year, 11, 1),
                'name' => 'Día de Todos los Santos / All Saints Day',
                'scope' => 'Nacional'
            ],
            [
                'date' => Carbon::create($year, 12, 6),
                'name' => 'Día de la Constitución Española / Constitution Day',
                'scope' => 'Nacional'
            ],
            [
                'date' => Carbon::create($year, 12, 8),
                'name' => 'Inmaculada Concepción / Immaculate Conception',
                'scope' => 'Nacional'
            ],
            [
                'date' => Carbon::create($year, 12, 25),
                'name' => 'Natividad del Señor / Christmas Day',
                'scope' => 'Nacional'
            ],

            // --- Festivos Insulares ---
            [
                'date' => Carbon::create($year, 2, 2),
                'name' => 'Virgen de la Candelaria (Tenerife)',
                'scope' => 'Insular'
            ],
            // Gran Canaria - Nuestra Señora del Pino (8 de septiembre)
            [
                'date' => $this->adjustForWeekend(Carbon::create($year, 9, 8), 'Nuestra Señora del Pino (Gran Canaria)'),
                'name' => 'Nuestra Señora del Pino (Gran Canaria)',
                'scope' => 'Insular'
            ],
            // Lanzarote - Nuestra Señora de los Dolores (15 de septiembre)
            [
                'date' => $this->adjustForWeekend(Carbon::create($year, 9, 15), 'Nuestra Señora de los Dolores (Lanzarote)'),
                'name' => 'Nuestra Señora de los Dolores (Lanzarote)',
                'scope' => 'Insular'
            ],
            // Fuerteventura - Nuestra Señora de la Peña (tercer viernes de septiembre)
            [
                'date' => $this->getThirdFridayOfSeptember($year),
                'name' => 'Nuestra Señora de la Peña (Fuerteventura)',
                'scope' => 'Insular'
            ],
            // La Gomera - Nuestra Señora de Guadalupe (lunes después del primer sábado de octubre)
            [
                'date' => $this->getMondayAfterFirstSaturdayOfOctober($year),
                'name' => 'Nuestra Señora de Guadalupe (La Gomera)',
                'scope' => 'Insular'
            ],
        ];

        // Crear excepciones de disponibilidad para cada festivo
        foreach ($holidays as $holiday) {
            AvailabilityException::create([
                'date' => $holiday['date'],
                'is_available' => false, // No disponible en festivos
                'reason' => $holiday['name'] . ' (' . $holiday['scope'] . ')',
            ]);
            
            $this->command->info('Creado festivo: ' . $holiday['name'] . ' - ' . $holiday['date']->format('Y-m-d'));
        }
    }

    /**
     * Obtiene la fecha del Jueves Santo para un año dado
     */
    private function getEasterThursday($year)
    {
        // Calculamos el domingo de Pascua y restamos 3 días
        return Carbon::createFromTimestamp(easter_date($year))->subDays(3);
    }

    /**
     * Obtiene la fecha del Viernes Santo para un año dado
     */
    private function getEasterFriday($year)
    {
        // Calculamos el domingo de Pascua y restamos 2 días
        return Carbon::createFromTimestamp(easter_date($year))->subDays(2);
    }

    /**
     * Obtiene el tercer viernes de septiembre
     */
    private function getThirdFridayOfSeptember($year)
    {
        $date = Carbon::create($year, 9, 1);
        // Ir al primer viernes
        while ($date->dayOfWeek != Carbon::FRIDAY) {
            $date->addDay();
        }
        // Ir al tercer viernes (sumando 14 días)
        return $date->addDays(14);
    }

    /**
     * Obtiene el lunes después del primer sábado de octubre
     */
    private function getMondayAfterFirstSaturdayOfOctober($year)
    {
        $date = Carbon::create($year, 10, 1);
        // Ir al primer sábado
        while ($date->dayOfWeek != Carbon::SATURDAY) {
            $date->addDay();
        }
        // Ir al lunes siguiente (sumando 2 días)
        return $date->addDays(2);
    }

    /**
     * Ajusta la fecha si cae en fin de semana (la mueve al lunes siguiente)
     */
    private function adjustForWeekend($date, $holidayName)
    {
        if ($date->isWeekend()) {
            $this->command->info("Festivo '$holidayName' cae en fin de semana. Movido al lunes siguiente.");
            return $date->next(Carbon::MONDAY);
        }
        return $date;
    }
} 