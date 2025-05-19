<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Models\Brand;
use App\Helpers\ImageHelper;

class AppointmentController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display the appointment booking form
     */
    public function bookingForm()
    {
        $services = Service::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        return view('appointments.book', compact('services', 'brands'));
    }

    /**
     * Obtener lista de citas
     */
    public function index(Request $request)
    {
        $query = Appointment::with('service')->latest();
        
        // Filtrar por estado si se especifica
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Filtrar por fecha si se especifica
        if ($request->has('date') && !empty($request->date)) {
            $date = Carbon::parse($request->date)->format('Y-m-d');
            $query->whereDate('start_time', $date);
        }
        
        // Filtrar por servicio si se especifica
        if ($request->has('service_id') && !empty($request->service_id)) {
            $query->where('service_id', $request->service_id);
        }
        
        // Obtener citas paginadas
        $appointments = $query->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    /**
     * Crear una nueva cita
     */
    public function store(Request $request)
    {
        // Log para depuración
        Log::info('Datos recibidos para crear cita:', $request->all());
        
        // Log file info if present
        if ($request->hasFile('equipment_photo')) {
            Log::info('Photo received:', [
                'originalName' => $request->file('equipment_photo')->getClientOriginalName(),
                'size' => $request->file('equipment_photo')->getSize(),
                'mimeType' => $request->file('equipment_photo')->getMimeType(),
            ]);
        }
        
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'client_first_name' => 'required|string|max:120|regex:/^\S*$/u',
            'client_last_name' => 'required|string|max:135|regex:/^\S*$/u',
            'client_email' => 'required|email|max:255',
            // Phone validation can be complex due to intl-tel-input sending E.164 format
            // Basic check for now, consider a custom rule or library like `propaganistas/laravel-phone` for stricter validation
            'client_phone' => 'required|string|max:25', // Increased max length for E.164
            'start_time' => 'required|date_format:Y-m-d H:i:s|after:today', // Use after:today for simplicity with Flatpickr sending Y-m-d H:i:s
            // New Fields Validation
            'brand_id' => 'required|exists:brands,id',
            'issue_description' => 'required|string',
            'address' => 'required|string',
            'equipment_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // Optional image upload validation (Max 10MB)
            'notes' => 'nullable|string', // Keep notes nullable
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Obtener el servicio para calcular la duración y final de la cita
        $service = Service::findOrFail($request->service_id);
        $validatedData = $validator->validated(); // Get validated data
        Log::info('Servicio encontrado:', [
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_duration' => $service->duration
        ]);
        
        try {
            // Start time is already validated as Y-m-d H:i:s
            $startTime = Carbon::parse($validatedData['start_time']);
            $endTime = $startTime->copy()->addMinutes($service->duration);
            
            Log::info('Cálculo de horarios:', [
                'start_time_string' => $validatedData['start_time'],
                'parsed_start_time' => $startTime->toDateTimeString(),
                'calculated_end_time' => $endTime->toDateTimeString(),
                'duration_minutes' => $service->duration
            ]);
        } catch (\Exception $e) {
            Log::error('Error al analizar fecha/hora (post-validation): ' . $e->getMessage(), [
                'start_time_input' => $validatedData['start_time'],
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Formato de fecha y hora inválido. Por favor, inténtelo de nuevo.'
            ], 422);
        }
        
        // Verificar si ya existe una cita en ese horario (excluding cancelled/completed)
        $conflictQuery = Appointment::where(function($query) use ($startTime, $endTime) {
            $query->where(function($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });
        })
        ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED]);
        
        // Log the conflict query
        Log::info('SQL para verificar conflictos:', [
            'query' => $conflictQuery->toSql(),
            'bindings' => $conflictQuery->getBindings()
        ]);
        
        $existingAppointment = $conflictQuery->first();
        
        if ($existingAppointment) {
            Log::warning('Conflicto de horario detectado:', [
                'new_start' => $startTime->toDateTimeString(),
                'new_end' => $endTime->toDateTimeString(),
                'conflicting_appointment_id' => $existingAppointment->id,
                'conflicting_start' => $existingAppointment->start_time->toDateTimeString(),
                'conflicting_end' => $existingAppointment->end_time->toDateTimeString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una cita programada en este horario.'
            ], 422);
        }
        
        // Handle file upload before transaction, but ensure cleanup on failure
        $photoPath = null;
        if ($request->hasFile('equipment_photo') && $request->file('equipment_photo')->isValid()) {
            try {
                // Use ImageHelper instead of direct storage
                $photoPath = ImageHelper::storeAndResizeLocally($request->file('equipment_photo'), 'appointment_photos');
                if (!$photoPath) {
                    Log::error('Failed to store and resize appointment photo.');
                    return response()->json(['success' => false, 'message' => 'Error al procesar la foto.'], 500);
                }
                Log::info('Photo processed and stored at: ' . $photoPath);
            } catch (Throwable $uploadError) {
                Log::error('Error during photo processing: ' . $uploadError->getMessage());
                return response()->json(['success' => false, 'message' => 'Error al procesar la foto.'], 500);
            }
        }
        
        try {
            $appointment = $this->transactionService->run(
                // 1. Database operations
                function () use ($validatedData, $startTime, $endTime, $photoPath, $service) {
                    $appointmentData = [
                        'uuid' => (string) Str::uuid(),
                        'service_id' => $validatedData['service_id'],
                        'brand_id' => $validatedData['brand_id'],
                        'client_first_name' => ucfirst(strtolower($validatedData['client_first_name'])),
                        'client_last_name' => ucfirst(strtolower($validatedData['client_last_name'])),
                        'client_email' => $validatedData['client_email'],
                        'client_phone' => $validatedData['client_phone'], // Already validated
                        'notes' => $validatedData['notes'] ?? null,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'issue_description' => $validatedData['issue_description'],
                        'address' => $validatedData['address'],
                        'equipment_photo_path' => $photoPath, // Save the path or null
                        'status' => Appointment::STATUS_PENDING
                    ];

                    $createdAppointment = Appointment::create($appointmentData);
                    Log::info('Appointment created within transaction', ['id' => $createdAppointment->id]);
                    return $createdAppointment;
                },
                // 2. Post-Commit actions (optional)
                function ($createdAppointment) {
                    Log::info('Appointment transaction committed successfully.', ['id' => $createdAppointment->id]);
                    $this->sendConfirmationEmail($createdAppointment); // Enviar correo después de confirmar transacción
                },
                // 3. On Error actions (before rollback)
                function (Throwable $dbError) use ($photoPath) {
                    // Cleanup uploaded photo if DB operation failed
                    if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                        Storage::disk('public')->delete($photoPath);
                        Log::warning('Rolled back photo upload due to DB error: ' . $photoPath);
                    }
                }
            );

            // If successful, return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Cita creada correctamente. Será contactado para confirmar la cita y/o solicitar más detalles.',
                'data' => $appointment->load('service') // Load service relationship
            ], 201);

        } catch (Throwable $e) {
            // Catch exceptions re-thrown by TransactionService
            Log::error('Error during appointment creation transaction: ' . $e->getMessage(), ['exception' => $e]);

            // Ensure photo is cleaned up even if the $onError handler within transactionService failed
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                 Storage::disk('public')->delete($photoPath);
                 Log::warning('Ensured photo cleanup after transaction exception: ' . $photoPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cita. Por favor, inténtelo de nuevo.'
            ], 500);
        }
    }

    /**
     * Obtener detalles de una cita específica
     */
    public function show($id)
    {
        $appointment = Appointment::with('service')->find($id);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $appointment
        ]);
    }

    /**
     * Actualizar una cita existente
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            Log::warning('Intentando actualizar cita inexistente:', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada'
            ], 404);
        }
        
        Log::info('Petición de actualización de cita:', [
            'appointment_id' => $id,
            'request_data' => $request->all()
        ]);
        
        // Add validation rules for new fields (mostly optional on update)
        $validator = Validator::make($request->all(), [
            'service_id' => 'sometimes|exists:services,id',
            'client_first_name' => 'sometimes|string|max:120|regex:/^\S*$/u',
            'client_last_name' => 'sometimes|string|max:135|regex:/^\S*$/u',
            'client_email' => 'sometimes|email|max:255',
            'client_phone' => 'sometimes|nullable|string|max:25', // Allow null/update
            'notes' => 'sometimes|nullable|string',
            'start_time' => 'sometimes|date_format:Y-m-d H:i:s',
            'status' => 'sometimes|in:new,pending,confirmed,cancelled,completed',
            // New fields - making them optional on update unless explicitly required
            'brand_id' => 'sometimes|nullable|exists:brands,id',
            'issue_description' => 'sometimes|nullable|string',
            'address' => 'sometimes|nullable|string',
            // Photo update might need separate handling/route if complex
        ]);

        if ($validator->fails()) {
            Log::error('Validación fallida en actualización:', [
                'id' => $id,
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Get validated data, excluding nulls unless explicitly needed
        $validatedData = $validator->validated();
        Log::info('Datos validados para actualización:', [
            'appointment_id' => $id,
            'validated_data' => $validatedData
        ]);

        try {
            $updatedAppointment = $this->transactionService->run(
                // 1. Database operations
                function () use ($appointment, $validatedData, $request, $id) {
                    $originalStartTime = $appointment->start_time;
                    $originalServiceId = $appointment->service_id;

                    // Recalculate end time only if start time or service changes
                    $recalculateTimes = false;
                    $startTime = $originalStartTime; // Default to original
                    $serviceId = $originalServiceId; // Default to original

                    if ($request->has('start_time')) {
                        $newStartTimeParsed = Carbon::parse($validatedData['start_time']);
                        if ($newStartTimeParsed->ne($originalStartTime)) {
                            $recalculateTimes = true;
                            $startTime = $newStartTimeParsed;
                            Log::info('Nuevo horario de inicio detectado:', [
                                'appointment_id' => $id,
                                'original_start' => $originalStartTime->toDateTimeString(),
                                'new_start' => $startTime->toDateTimeString()
                            ]);
                        }
                    }

                    if ($request->has('service_id') && $validatedData['service_id'] != $originalServiceId) {
                        $recalculateTimes = true;
                        $serviceId = $validatedData['service_id'];
                        $service = Service::findOrFail($serviceId); // Fetch new service within transaction
                        Log::info('Nuevo servicio seleccionado:', [
                            'appointment_id' => $id,
                            'original_service_id' => $originalServiceId,
                            'new_service_id' => $serviceId,
                            'new_service_duration' => $service->duration
                        ]);
                    } else {
                        $service = $appointment->service; // Use existing relationship if service not changing
                    }

                    $endTime = $appointment->end_time; // Default to original

                    if ($recalculateTimes) {
                        $endTime = $startTime->copy()->addMinutes($service->duration);
                        Log::info('Recalculando horarios:', [
                            'appointment_id' => $id,
                            'new_start' => $startTime->toDateTimeString(),
                            'new_end' => $endTime->toDateTimeString(),
                            'service_duration' => $service->duration
                        ]);

                        // Check for conflicts (excluding self)
                        $conflictQuery = Appointment::where('id', '!=', $id)
                            ->where(function ($query) use ($startTime, $endTime) {
                                $query->where('start_time', '<', $endTime)
                                      ->where('end_time', '>', $startTime);
                            })
                            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED]);
                        
                        // Log the conflict query
                        Log::info('SQL para verificar conflictos de actualización:', [
                            'appointment_id' => $id,
                            'query' => $conflictQuery->toSql(),
                            'bindings' => $conflictQuery->getBindings()
                        ]);
                        
                        $existingAppointment = $conflictQuery->first();

                        if ($existingAppointment) {
                            Log::warning('Conflicto de horario en actualización:', [
                                'appointment_id' => $id,
                                'new_start' => $startTime->toDateTimeString(),
                                'new_end' => $endTime->toDateTimeString(),
                                'conflicting_appointment_id' => $existingAppointment->id,
                                'conflicting_start' => $existingAppointment->start_time->toDateTimeString(),
                                'conflicting_end' => $existingAppointment->end_time->toDateTimeString()
                            ]);
                            // Throw an exception to trigger rollback
                            throw new \RuntimeException('Ya existe otra cita programada en este horario.', 422);
                        }
                        // Update times if they were recalculated
                        $appointment->start_time = $startTime;
                        $appointment->end_time = $endTime;
                    }

                    // Update service ID if provided
                    if ($request->has('service_id')) {
                        $appointment->service_id = $validatedData['service_id'];
                    }

                    // Update other fields if they are present in the request
                    $updateData = array_filter($request->only([
                        'client_first_name',
                        'client_last_name',
                        'client_email',
                        'client_phone',
                        'notes',
                        'brand_id',
                        'issue_description',
                        'address',
                        'status' // Status update is handled here now
                    ]), function($value) { return $value !== null; }); // Filter out potential nulls if desired

                    // Apply formatting if names are being updated
                    if (isset($updateData['client_first_name'])) {
                        $updateData['client_first_name'] = ucfirst(strtolower($updateData['client_first_name']));
                    }
                    if (isset($updateData['client_last_name'])) {
                        $updateData['client_last_name'] = ucfirst(strtolower($updateData['client_last_name']));
                    }

                    $appointment->fill($updateData); // Mass assign the provided fields

                    $appointment->save();
                    Log::info('Appointment updated within transaction', ['id' => $appointment->id]);
                    return $appointment; // Return the updated model
                }
                // No specific onCommit or onError needed for basic update
            );

            return response()->json([
                'success' => true,
                'message' => 'Cita actualizada correctamente',
                'data' => $updatedAppointment->fresh()->load('service') // Refresh data
            ]);

        } catch (\RuntimeException $re) {
             // Catch conflict error specifically
             Log::warning('Conflict during appointment update: ' . $re->getMessage(), ['id' => $id]);
             return response()->json([
                 'success' => false,
                 'message' => $re->getMessage() // Use message from exception
             ], $re->getCode() ?: 422); // Use code from exception or default
        } catch (Throwable $e) {
            Log::error('Error during appointment update transaction: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la cita. Por favor, inténtelo de nuevo.'
            ], 500);
        }
    }

    /**
     * Cancelar una cita
     */
    public function cancel($id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada'
            ], 404);
        }
        
        if ($appointment->status === Appointment::STATUS_CANCELLED) { // Use constant
            return response()->json([
                'success' => false,
                'message' => 'Esta cita ya está cancelada'
            ], 422);
        }
        
        if ($appointment->status === Appointment::STATUS_COMPLETED) { // Use constant
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar una cita ya completada'
            ], 422);
        }
        
        try {
            $cancelledAppointment = $this->transactionService->run(
                function () use ($appointment) {
                    $appointment->status = Appointment::STATUS_CANCELLED;
                    $appointment->save();
                    Log::info('Appointment cancelled within transaction', ['id' => $appointment->id]);
                    return $appointment;
                },
                // Añadir onCommit para enviar el correo de cancelación
                function ($cancelledAppt) {
                    $this->sendCancellationEmail($cancelledAppt);
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Cita cancelada correctamente',
                'data' => $cancelledAppointment->fresh()->load('service')
            ]);

        } catch (Throwable $e) {
            Log::error('Error cancelling appointment: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Error al cancelar la cita.'], 500);
        }
    }

    /**
     * Eliminar una cita
     */
    public function destroy($id)
    {
        // Find appointment first to get photo path if needed
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada'
            ], 404);
        }
        
        // Store photo path before transaction potentially deletes the appointment object
        $photoPathToDelete = $appointment->equipment_photo_path;

        try {
            $this->transactionService->run(
                // 1. Database and related operations
                function () use ($appointment, $photoPathToDelete, $id) {
                    // Delete the appointment record
                    $appointment->delete();
                    Log::info('Appointment deleted from DB', ['id' => $id]);

                    // Delete associated photo within the same transaction scope
                    if ($photoPathToDelete && Storage::disk('public')->exists($photoPathToDelete)) {
                        $deleted = Storage::disk('public')->delete($photoPathToDelete);
                        if ($deleted) {
                            Log::info('Deleted associated photo: ' . $photoPathToDelete);
                        } else {
                            Log::warning('Failed to delete associated photo (might not be critical): ' . $photoPathToDelete);
                            // Decide if failure to delete photo should cause rollback (usually not)
                            // throw new \RuntimeException("Failed to delete associated file.");
                        }
                    }
                    // No return value needed for delete
                }
                // No specific onCommit or onError needed for delete
            );

            return response()->json([
                'success' => true,
                'message' => 'Cita eliminada correctamente'
            ]);

        } catch (Throwable $e) {
            Log::error('Error deleting appointment: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cita.'
            ], 500);
        }
    }
    
    /**
     * Obtener lista de servicios disponibles
     */
    public function getServices()
    {
        $services = Service::where('active', true)->get();
        
        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }
    
    /**
     * Confirmar una cita (cambiar su estado a confirmado)
     */
    public function confirm($id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada'
            ], 404);
        }
        
        if ($appointment->status !== Appointment::STATUS_PENDING) { // Use constant
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden confirmar citas pendientes'
            ], 422);
        }
        
        try {
            $confirmedAppointment = $this->transactionService->run(
                function () use ($appointment) {
                    $appointment->status = Appointment::STATUS_CONFIRMED;
                    $appointment->save();
                    Log::info('Appointment confirmed within transaction', ['id' => $appointment->id]);
                    return $appointment;
                },
                // Añadir onCommit para enviar el correo de confirmación al cliente
                function ($confirmedAppt) {
                    $this->sendAppointmentStatusUpdateEmail($confirmedAppt, 'confirmed');
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Cita confirmada correctamente',
                'data' => $confirmedAppointment->fresh()->load('service')
            ]);

        } catch (Throwable $e) {
            Log::error('Error confirming appointment: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Error al confirmar la cita.'], 500);
        }
    }
    
    /**
     * Marcar una cita como completada
     */
    public function complete($id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada'
            ], 404);
        }
        
        if ($appointment->status !== Appointment::STATUS_CONFIRMED) { // Use constant
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden completar citas confirmadas'
            ], 422);
        }
        
        try {
            $completedAppointment = $this->transactionService->run(
                function () use ($appointment) {
                    $appointment->status = Appointment::STATUS_COMPLETED;
                    $appointment->save();
                    Log::info('Appointment completed within transaction', ['id' => $appointment->id]);
                    return $appointment;
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Cita marcada como completada',
                'data' => $completedAppointment->fresh()->load('service')
            ]);

        } catch (Throwable $e) {
            Log::error('Error completing appointment: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Error al marcar la cita como completada.'], 500);
        }
    }
    
    /**
     * Método para enviar correo de nueva cita al cliente y a la empresa
     */
    private function sendConfirmationEmail(Appointment $appointment)
    {
        try {
            // Obtener los datos de la empresa
            $companyData = \App\Models\CompanyData::first();
            
            if (!$companyData) {
                Log::error('No company data found for sending appointment confirmation', ['appointment_id' => $appointment->id]);
                return;
            }
            
            // Cargar relaciones necesarias
            $appointment->load(['service', 'brand']);
            
            // Enviar correo al cliente
            Mail::to($appointment->client_email)
                ->send(new \App\Mail\AppointmentConfirmation($appointment, $companyData));
            
            // Enviar correo a la empresa
            if ($companyData->email) {
                Mail::to($companyData->email)
                    ->send(new \App\Mail\AppointmentConfirmation($appointment, $companyData, true));
            }
            
            Log::info('Appointment confirmation emails sent successfully', ['appointment_id' => $appointment->id]);
        } catch (\Exception $e) {
            Log::error('Error sending appointment confirmation emails', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Método para enviar correo de actualización de estado (confirmación/cancelación)
     */
    private function sendAppointmentStatusUpdateEmail(Appointment $appointment, $status)
    {
        try {
            // Obtener los datos de la empresa
            $companyData = \App\Models\CompanyData::first();
            
            if (!$companyData) {
                Log::error('No company data found for sending status update email', ['appointment_id' => $appointment->id]);
                return;
            }
            
            // Cargar relaciones necesarias
            $appointment->load(['service', 'brand']);
            
            // Determinar qué tipo de correo enviar
            if ($status === 'confirmed') {
                // Enviar correo de confirmación
                Mail::to($appointment->client_email)
                    ->send(new \App\Mail\AppointmentConfirmed($appointment, $companyData));
            } else if ($status === 'cancelled') {
                // Enviar correo de cancelación
                Mail::to($appointment->client_email)
                    ->send(new \App\Mail\AppointmentCancelled($appointment, $companyData));
            }
            
            Log::info("Appointment {$status} email sent successfully", ['appointment_id' => $appointment->id]);
        } catch (\Exception $e) {
            Log::error("Error sending appointment {$status} email", [
                'appointment_id' => $appointment->id,
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Método para enviar correo de cancelación
     */
    private function sendCancellationEmail(Appointment $appointment)
    {
        $this->sendAppointmentStatusUpdateEmail($appointment, 'cancelled');
    }
}
