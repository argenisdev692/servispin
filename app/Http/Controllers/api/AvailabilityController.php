<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AvailabilityRule;
use App\Models\AvailabilityException;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Obtener las reglas de disponibilidad
     */
    public function getRules()
    {
        $rules = AvailabilityRule::all();
        return response()->json([
            'success' => true,
            'data' => $rules
        ]);
    }
    
    /**
     * Obtener excepciones de disponibilidad
     */
    public function getExceptions()
    {
        $exceptions = AvailabilityException::all();
        return response()->json([
            'success' => true,
            'data' => $exceptions
        ]);
    }
    
    /**
     * Obtener slots de tiempo disponibles para un servicio y fecha específica
     */
    public function getTimeSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after:' . now()->format('Y-m-d'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $serviceId = $request->service_id;
        // Define the target timezone
        $targetTimezone = 'Atlantic/Canary';

        // Parse the date string in the target timezone
        $parsedDate = Carbon::parse($request->date, $targetTimezone); 
        $date = $parsedDate->format('Y-m-d');
        $dayOfWeek = $parsedDate->dayOfWeek; // 0 (domingo) a 6 (sábado)
        
        // Log the requested date and calculated day of week
        \Log::debug("Requested Date: {$request->date}, Parsed Date: {$date}, Calculated DayOfWeek: {$dayOfWeek}, Target Timezone: {$targetTimezone}");

        // Verificar si hay una excepción para este día
        $exception = AvailabilityException::where('date', $date)->first();
        if ($exception && !$exception->is_available) {
            return response()->json([
                'success' => true,
                'message' => $exception->reason ?? 'No estamos disponibles en esta fecha',
                'data' => []
            ]);
        }
        
        // Obtener regla de disponibilidad para este día de la semana
        $rule = AvailabilityRule::where('day_of_week', $dayOfWeek)->first();

        // Log the found rule
        \Log::debug("Rule Found:", optional($rule)->toArray() ?? ['message' => 'No rule found']);

        if (!$rule || !$rule->is_available) {
            return response()->json([
                'success' => true,
                'message' => 'No estamos disponibles en este día de la semana',
                'data' => []
            ]);
        }
        
        // Obtener el servicio para calcular la duración
        $service = Service::find($serviceId);
        $serviceDuration = $service->duration; // duración en minutos
        
        // Generar slots de tiempo
        // Parse the target date in the correct timezone
        $targetDate = Carbon::parse($date, $targetTimezone)->startOfDay();

        // Parse the rule times (which seem to include date/timezone info from the model/db)
        // Ensure we parse them in the application's timezone (or UTC if they are Z) and then convert to target timezone
        $ruleStartTime = Carbon::parse($rule->start_time)->setTimezone($targetTimezone);
        $ruleEndTime = Carbon::parse($rule->end_time)->setTimezone($targetTimezone);

        // Combine the target date with the rule times in the target timezone
        $startTime = $targetDate->copy()->setTime($ruleStartTime->hour, $ruleStartTime->minute, $ruleStartTime->second);
        $endTime = $targetDate->copy()->setTime($ruleEndTime->hour, $ruleEndTime->minute, $ruleEndTime->second);

        // Ensure end time is on the same day or next if it crosses midnight
        if ($endTime->lessThan($startTime)) {
             $endTime->addDay();
        }
        
        // Log the calculated start and end times for slot generation in the target timezone
        \Log::debug("Calculated Slot StartTime: {$startTime->format('Y-m-d H:i:s P')}, EndTime: {$endTime->format('Y-m-d H:i:s P')}");
        
        // Obtener todas las citas para este día
        // Ensure appointment times are compared in the correct timezone
        $dayStart = $startTime->copy()->startOfDay(); // Already in target timezone
        $dayEnd = $startTime->copy()->endOfDay();     // Already in target timezone

        $appointments = Appointment::where('start_time', '>=', $dayStart)
            ->where('start_time', '<=', $dayEnd)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();
        
        $timeSlots = [];
        $currentTime = $startTime->copy(); // Use copy() for Carbon
        
        // Crear slots de 30 minutos
        $slotDuration = 30; // minutos
        
        while ($currentTime->copy()->addMinutes($serviceDuration) <= $endTime) {
            $slot = [
                'start_time' => $currentTime->format('H:i'),
                'end_time' => $currentTime->copy()->addMinutes($serviceDuration)->format('H:i'),
                'available' => true
            ];
            
            // Verificar si este slot está disponible
            foreach ($appointments as $appointment) {
                // Parse appointment times and convert to the target timezone for comparison
                $appointmentStart = Carbon::parse($appointment->start_time)->setTimezone($targetTimezone);
                $appointmentEnd = Carbon::parse($appointment->end_time)->setTimezone($targetTimezone);
                
                // Si hay solapamiento, el slot no está disponible
                if (
                    ($currentTime >= $appointmentStart && $currentTime < $appointmentEnd) ||
                    ($currentTime->copy()->addMinutes($serviceDuration) > $appointmentStart && 
                     $currentTime->copy()->addMinutes($serviceDuration) <= $appointmentEnd) ||
                    ($currentTime <= $appointmentStart && 
                     $currentTime->copy()->addMinutes($serviceDuration) >= $appointmentEnd)
                ) {
                    $slot['available'] = false;
                    break;
                }
            }
            
            if ($slot['available']) {
                $timeSlots[] = $slot;
            }
            
            $currentTime->addMinutes($slotDuration);
        }
        
        return response()->json([
            'success' => true,
            // Format the output times correctly using the target timezone
            'data' => collect($timeSlots)->map(function($slot) use ($startTime, $targetTimezone) {
                // Parse the H:i string to get hours and minutes
                list($hour, $minute) = explode(':', $slot['start_time']);
                // Create the final Carbon object by taking the date from $startTime (already in target timezone)
                // and setting the specific hour/minute for this slot.
                $slotTime = $startTime->copy()->setTime($hour, $minute, 0);
                
                return [
                    // Send the full ISO 8601 time string including target timezone offset
                    'time' => $slotTime->format('Y-m-d H:i:s'), 
                    'formatted_time' => $slotTime->format('H:i'), // Changed from 'h:i A' to 24-hour format
                    'available' => $slot['available']
                ];
            })->filter(function($slot) {
                return $slot['available'];
            })->values()->all()
        ]);
    }
    
    /**
     * Crear o actualizar una regla de disponibilidad
     */
    public function saveRule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'is_available' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $rule = AvailabilityRule::updateOrCreate(
            ['day_of_week' => $request->day_of_week],
            [
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'is_available' => $request->is_available,
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Regla de disponibilidad guardada correctamente',
            'data' => $rule
        ]);
    }
    
    /**
     * Crear o actualizar una excepción de disponibilidad
     */
    public function saveException(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'is_available' => 'required|boolean',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $date = Carbon::parse($request->date)->format('Y-m-d');
        
        $exception = AvailabilityException::updateOrCreate(
            ['date' => $date],
            [
                'is_available' => $request->is_available,
                'reason' => $request->reason,
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Excepción de disponibilidad guardada correctamente',
            'data' => $exception
        ]);
    }
    
    /**
     * Eliminar una excepción de disponibilidad
     */
    public function deleteException($id)
    {
        $exception = AvailabilityException::find($id);
        
        if (!$exception) {
            return response()->json([
                'success' => false,
                'message' => 'Excepción no encontrada'
            ], 404);
        }
        
        $exception->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Excepción eliminada correctamente'
        ]);
    }
}
