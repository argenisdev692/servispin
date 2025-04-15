<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentCancelled;

class AppointmentCalendarController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display the calendar view.
     */
    public function index()
    {
        // We just need to return the view, the calendar data will be loaded via AJAX
        return view('full-calendar.calendar'); 
    }

    /**
     * Fetch appointments as events for FullCalendar.
     */
    public function events(Request $request)
    {
        // Fetch appointments within the range requested by FullCalendar (start, end parameters)
        // Or fetch all relevant appointments if no range is provided initially
        $start = $request->query('start') ? Carbon::parse($request->query('start'))->startOfDay() : now()->subMonth();
        $end = $request->query('end') ? Carbon::parse($request->query('end'))->endOfDay() : now()->addMonth();

        $appointments = Appointment::with('service') // Eager load service
            ->where(function($query) use ($start, $end) {
                // Find appointments that *overlap* with the requested range
                $query->where('start_time', '<=', $end)
                      ->where('end_time', '>=', $start);
            })
            // Optionally filter by status if needed (e.g., exclude cancelled/completed)
            // ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_COMPLETED])
            ->get();

        $events = $appointments->map(function (Appointment $appointment) {
            // Map appointment data to FullCalendar event format
            $color = '#3b82f6'; // Default blue (e.g., for new/pending)
            switch ($appointment->status) {
                case Appointment::STATUS_CONFIRMED:
                    $color = '#10b981'; // Green
                    break;
                case Appointment::STATUS_CANCELLED:
                    $color = '#ef4444'; // Red
                    break;
                case Appointment::STATUS_COMPLETED:
                    $color = '#6b7280'; // Gray
                    break;
            }

            return [
                'id' => $appointment->id,
                'title' => $appointment->service->name ?? 'Servicio Desconocido', // Ahora el título es el servicio
                'start' => $appointment->start_time->toIso8601String(), // Use ISO 8601 format
                'end' => $appointment->end_time->toIso8601String(),
                'color' => $color,
                // Add any other custom properties you might want in the eventClick popup
                'extendedProps' => [
                    'service' => $appointment->service->name ?? 'N/A',
                    'clientName' => $appointment->client_first_name . ' ' . $appointment->client_last_name, // Nombre del cliente
                    'clientEmail' => $appointment->client_email,
                    'clientPhone' => $appointment->client_phone,
                    'status' => $appointment->status,
                    'notes' => $appointment->notes,
                    'address' => $appointment->address,
                    'issue' => $appointment->issue_description,
                    'equipmentPhotoPath' => $appointment->equipment_photo_path,
                    // Add more details as needed
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Update appointment time via drag-and-drop.
     */
    public function update(Request $request, Appointment $appointment)
    {
        // Validate the incoming start and end times
        $validator = Validator::make($request->all(), [
            'start' => 'required|date',
            'end' => 'required|date|after:start',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid date format provided.', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        try {
            $updatedAppointment = $this->transactionService->run(
                // 1. Database operations
                function () use ($appointment, $validatedData) {
                    // Parse new times (ensure they are treated in the application's timezone)
                    $newStartTime = Carbon::parse($validatedData['start']);
                    $newEndTime = Carbon::parse($validatedData['end']);
                    $serviceDuration = $appointment->service->duration; // Get duration from related service

                    // Optional: Recalculate end time based on start time + duration to ensure consistency
                    // Comment this out if FullCalendar always sends the correct end time based on duration
                    // $newEndTime = $newStartTime->copy()->addMinutes($serviceDuration);

                    // Ensure the duration hasn't accidentally changed (sanity check)
                    if ($newStartTime->diffInMinutes($newEndTime) != $serviceDuration) {
                        // Use a more specific exception maybe, or just RuntimeException
                        throw new \RuntimeException('La duración de la cita no puede cambiar.', 422);
                    }

                    // Check for conflicts with other appointments (excluding the current one)
                    $existingAppointment = Appointment::where('id', '!=', $appointment->id)
                        ->where(function($query) use ($newStartTime, $newEndTime) {
                            $query->where('start_time', '<', $newEndTime)
                                  ->where('end_time', '>', $newStartTime);
                        })
                        // Consider only relevant statuses for conflict checking
                        // Use constants if available and appropriate (e.g., Appointment::STATUS_NEW)
                        ->whereIn('status', ['pending', 'confirmed']) // Adjust statuses as needed
                        ->lockForUpdate() // Add pessimistic lock for concurrency safety
                        ->first();

                    if ($existingAppointment) {
                        throw new \RuntimeException('Conflicto de horario: Ya existe otra cita en este horario.', 409); // 409 Conflict
                    }

                    // Update the appointment
                    $appointment->start_time = $newStartTime;
                    $appointment->end_time = $newEndTime;
                    $appointment->save();

                    Log::info('Appointment updated via calendar drag-drop within transaction', ['id' => $appointment->id]);
                    return $appointment;
                },
                // Acción a ejecutar después de que la transacción se haya completado
                function ($updatedAppointment) {
                    try {
                        // Obtener los datos de la empresa
                        $companyData = \App\Models\CompanyData::first();
                        
                        if (!$companyData) {
                            // Si no hay datos de la empresa, registramos el error pero igualmente actualizamos el estado
                            \Log::error('No company data found for sending rescheduling email notification');
                            return;
                        }
                        
                        // Cargar las relaciones necesarias
                        $updatedAppointment->load(['service', 'brand']);
                        
                        // Enviar correo de reagendamiento
                        Mail::to($updatedAppointment->client_email)
                            ->send(new \App\Mail\AppointmentRescheduled($updatedAppointment, $companyData));
                        
                        Log::info('Rescheduling email sent for appointment', ['id' => $updatedAppointment->id]);
                    } catch (\Exception $e) {
                        // Log the error but don't fail the response
                        \Log::error('Error sending rescheduling email notification: ' . $e->getMessage());
                    }
                }
            );

            // If transaction is successful
            return response()->json(['message' => 'Cita actualizada correctamente. Se ha enviado un correo de notificación al cliente.']);

        } catch (\RuntimeException $re) {
            // Catch specific conflict/duration errors thrown from within the transaction
            Log::warning('Business logic error during calendar update: ' . $re->getMessage(), [
                'id' => $appointment->id,
                'code' => $re->getCode()
            ]);
            return response()->json(['message' => $re->getMessage()], $re->getCode() ?: 422);
        } catch (Throwable $e) {
            // Catch any other errors during the transaction
            Log::error('Error during calendar appointment update transaction: ' . $e->getMessage(), [
                'id' => $appointment->id,
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error al actualizar la cita en el calendario.'], 500);
        }
    }

    /**
     * Update the specified appointment's status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        // Validate request data
        $request->validate([
            'status' => 'required|in:Confirmed,Cancelled'
        ]);

        // Find the appointment
        $appointment = Appointment::findOrFail($id);

        // Skip if appointment is "New" or already has the requested status
        if ($appointment->status === 'New' || $appointment->status === $request->status) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede actualizar el estado de esta cita.',
            ], 422);
        }

        // Update the appointment status
        $oldStatus = $appointment->status;
        $appointment->status = $request->status;
        $appointment->save();

        // Send email notification based on the new status
        try {
            // Obtener los datos de la empresa
            $companyData = \App\Models\CompanyData::first();
            
            if (!$companyData) {
                // Si no hay datos de la empresa, registramos el error pero igualmente actualizamos el estado
                \Log::error('No company data found for sending email notification');
                $message = 'Estado actualizado, pero no se pudo enviar el correo por falta de datos de la empresa.';
            } else {
                // Cargar las relaciones necesarias
                $appointment->load(['service', 'brand']);
                
                if ($request->status === 'Confirmed') {
                    Mail::to($appointment->client_email)->send(new AppointmentConfirmed($appointment, $companyData));
                    $message = 'Cita confirmada exitosamente. Se ha enviado un correo de confirmación al cliente.';
                } else if ($request->status === 'Cancelled') {
                    Mail::to($appointment->client_email)->send(new AppointmentCancelled($appointment, $companyData));
                    $message = 'Cita rechazada exitosamente. Se ha enviado un correo de notificación al cliente.';
                }
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the response
            \Log::error('Error sending email notification: ' . $e->getMessage());
            $message = 'Estado actualizado, pero hubo un problema al enviar el correo de notificación.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'id' => $appointment->id,
                'old_status' => $oldStatus,
                'new_status' => $appointment->status,
            ]
        ]);
    }
}
