<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AvailabilityException;
use App\Models\AvailabilityRule;
use App\Models\Service;
use App\Services\SchedulingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AvailabilityController extends Controller
{
    protected SchedulingService $scheduling;

    public function __construct(SchedulingService $scheduling)
    {
        $this->scheduling = $scheduling;
    }

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
            'data' => $rules,
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
            'data' => $exceptions,
        ]);
    }

    /**
     * Obtener slots de tiempo disponibles para un servicio y fecha específica
     */
    public function getTimeSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after:'.now()->format('Y-m-d'),
            // Huso del cliente (FR-6). Opcional: el formulario presencial no lo
            // envía y debe seguir funcionando igual que siempre.
            'timezone' => 'sometimes|nullable|timezone',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $serviceId = $request->service_id;
        // Define the target timezone
        $targetTimezone = 'Atlantic/Canary';

        // Huso en el que se le muestran las horas al cliente. Si no lo manda,
        // cae al del negocio y así se le indica (FR-6): nunca se muestra una
        // hora sin decir de qué huso es. R-5 dice que este es el fallo más
        // probable del módulo y el más caro en reputación.
        $clientTimezone = $request->filled('timezone') ? $request->timezone : $targetTimezone;

        // Parse the date string in the target timezone
        $parsedDate = Carbon::parse($request->date, $targetTimezone);
        $date = $parsedDate->format('Y-m-d');
        $dayOfWeek = $parsedDate->dayOfWeek; // 0 (domingo) a 6 (sábado)

        // Log the requested date and calculated day of week
        \Log::debug("Requested Date: {$request->date}, Parsed Date: {$date}, Calculated DayOfWeek: {$dayOfWeek}, Target Timezone: {$targetTimezone}");

        // Verificar si hay una excepción para este día
        $exception = AvailabilityException::where('date', $date)->first();
        if ($exception && ! $exception->is_available) {
            return response()->json([
                'success' => true,
                'message' => $exception->reason ?? 'No estamos disponibles en esta fecha',
                'data' => [],
            ]);
        }

        // Obtener regla de disponibilidad para este día de la semana
        $rule = AvailabilityRule::where('day_of_week', $dayOfWeek)->first();

        // Log the found rule
        \Log::debug('Rule Found:', optional($rule)->toArray() ?? ['message' => 'No rule found']);

        if (! $rule || ! $rule->is_available) {
            return response()->json([
                'success' => true,
                'message' => 'No estamos disponibles en este día de la semana',
                'data' => [],
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
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->get();

        // El buffer depende del PAR de modalidades, no es una constante
        // (research #9b, plan §4b). El histórico de 240 min existe porque el
        // técnico conduce; una videollamada no requiere desplazarse, y aplicarle
        // 4h por delante y por detrás bloquearía 8,5h de agenda para una sesión
        // de 30 min — justo el tiempo muerto que el módulo remoto quiere
        // monetizar (spec §2). Presencial↔presencial se mantiene idéntico.
        $requestedModality = $service->is_remote
            ? Appointment::MODALITY_REMOTE
            : Appointment::MODALITY_ONSITE;

        // Map appointments with their buffer times for easier reference
        $appointmentBuffers = [];
        foreach ($appointments as $appointment) {
            $appointmentStart = Carbon::parse($appointment->start_time)->setTimezone($targetTimezone);
            $appointmentEnd = Carbon::parse($appointment->end_time)->setTimezone($targetTimezone);

            $bufferMinutes = $this->scheduling->bufferMinutesBetween(
                $appointment->modality ?? Appointment::MODALITY_ONSITE,
                $requestedModality
            );

            $bufferStart = $appointmentStart->copy()->subMinutes($bufferMinutes);
            $bufferEnd = $appointmentEnd->copy()->addMinutes($bufferMinutes);

            $appointmentBuffers[] = [
                'appointment_id' => $appointment->id,
                'buffer_minutes' => $bufferMinutes,
                'original_start' => $appointmentStart->format('Y-m-d H:i:s'),
                'original_end' => $appointmentEnd->format('Y-m-d H:i:s'),
                'buffer_start' => $bufferStart,
                'buffer_end' => $bufferEnd,
            ];
        }

        // Log appointment buffers for debugging
        if (count($appointmentBuffers) > 0) {
            $loggableBuffers = array_map(function ($buffer) {
                return [
                    'appointment_id' => $buffer['appointment_id'],
                    'original_start' => $buffer['original_start'],
                    'original_end' => $buffer['original_end'],
                    'buffer_start' => $buffer['buffer_start']->format('Y-m-d H:i:s'),
                    'buffer_end' => $buffer['buffer_end']->format('Y-m-d H:i:s'),
                ];
            }, $appointmentBuffers);
            \Log::debug("Appointment Buffers for date {$date}:", $loggableBuffers);
        }

        $timeSlots = [];
        $currentTime = $startTime->copy(); // Use copy() for Carbon

        // Crear slots de 30 minutos
        $slotDuration = 30; // minutos

        while ($currentTime->copy()->addMinutes($serviceDuration) <= $endTime) {
            $slotStart = $currentTime->copy();
            $slotEnd = $currentTime->copy()->addMinutes($serviceDuration);

            // Flag to check if slot is available
            $isAvailable = true;

            // Check if this slot is within any appointment buffer zone
            foreach ($appointmentBuffers as $buffer) {
                $bufferStart = $buffer['buffer_start'];
                $bufferEnd = $buffer['buffer_end'];

                // Check if the current slot overlaps with the appointment's buffer zone
                if (
                    // The slot starts during the buffer
                    ($slotStart >= $bufferStart && $slotStart <= $bufferEnd) ||
                    // The slot ends during the buffer
                    ($slotEnd >= $bufferStart && $slotEnd <= $bufferEnd) ||
                    // The slot contains the entire buffer
                    ($slotStart <= $bufferStart && $slotEnd >= $bufferEnd)
                ) {
                    $isAvailable = false;
                    break;
                }
            }

            // Only add slot if it's available
            if ($isAvailable) {
                $timeSlots[] = [
                    'start_time' => $slotStart->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'available' => true,
                ];
            }

            // Move to next slot
            $currentTime->addMinutes($slotDuration);
        }

        return response()->json([
            'success' => true,
            // El huso en que se muestran las horas, explícito y siempre presente:
            // el cliente tiene que poder ver de qué hora le estamos hablando.
            'timezone' => $clientTimezone,
            'business_timezone' => $targetTimezone,
            'timezone_matches_business' => $clientTimezone === $targetTimezone,
            // Format the output times correctly using the target timezone
            'data' => collect($timeSlots)->map(function ($slot) use ($startTime, $clientTimezone) {
                // Parse the H:i string to get hours and minutes
                [$hour, $minute] = explode(':', $slot['start_time']);
                // Create the final Carbon object by taking the date from $startTime (already in target timezone)
                // and setting the specific hour/minute for this slot.
                $slotTime = $startTime->copy()->setTime($hour, $minute, 0);

                // La misma instancia, vista desde el huso del cliente.
                $clientSlotTime = $slotTime->copy()->setTimezone($clientTimezone);

                return [
                    // 'time' NO cambia: es lo que el formulario devuelve en
                    // start_time y lo que el flujo presencial ya consume. Va en
                    // el huso del negocio, que es como se persiste (plan §9 R-5).
                    'time' => $slotTime->format('Y-m-d H:i:s'),
                    'formatted_time' => $slotTime->format('H:i'), // Changed from 'h:i A' to 24-hour format
                    // Campos nuevos, aditivos, para el formulario remoto (FR-6).
                    'client_time' => $clientSlotTime->format('Y-m-d H:i:s'),
                    'client_formatted_time' => $clientSlotTime->format('H:i'),
                    'client_timezone' => $clientTimezone,
                    'client_offset' => $clientSlotTime->format('P'),
                    'available' => true, // All slots in the array are available now
                ];
            })->values()->all(),
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
                'errors' => $validator->errors(),
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
            'data' => $rule,
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
                'errors' => $validator->errors(),
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
            'data' => $exception,
        ]);
    }

    /**
     * Eliminar una excepción de disponibilidad
     */
    public function deleteException($id)
    {
        $exception = AvailabilityException::find($id);

        if (! $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Excepción no encontrada',
            ], 404);
        }

        $exception->delete();

        return response()->json([
            'success' => true,
            'message' => 'Excepción eliminada correctamente',
        ]);
    }
}
