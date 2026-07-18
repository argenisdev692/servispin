<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentRescheduled;
use App\Mail\RemoteAssistanceCancelled;
use App\Models\Appointment;
use App\Models\AppointmentPaymentEvent;
use App\Models\Brand;
use App\Models\CompanyData;
use App\Models\Service;
use App\Services\MeetingLink\MeetingLinkProvider;
use App\Services\PaymentEventLogger;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AppointmentCalendarController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(
        TransactionService $transactionService,
        protected PaymentEventLogger $paymentEvents,
    ) {
        $this->transactionService = $transactionService;
    }

    /**
     * Display the calendar view.
     */
    public function index()
    {
        // Los eventos siguen cargándose por AJAX; esto es solo lo que necesita el
        // formulario de alta de cita remota (US-6): Cesar la crea desde un hueco
        // del calendario cuando el cliente llama por teléfono y paga por QR.
        return view('full-calendar.calendar', [
            'remoteServices' => Service::remote()->active()->orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'providerIsAutomatic' => app(MeetingLinkProvider::class)->isAutomatic(),
            'businessTimezone' => config('remote_assistance.business_timezone', 'Atlantic/Canary'),
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
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
            ->where(function ($query) use ($start, $end) {
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

            // FR-10: una remota tiene que distinguirse de una presencial de un
            // vistazo. El color ya lo usa el estado, así que la modalidad se
            // marca en el título y con el borde del evento: si Cesar confunde
            // una videollamada con una visita, o conduce para nada o deja
            // plantado a un cliente que ya pagó.
            $isRemote = $appointment->isRemote();

            return [
                'id' => $appointment->id,
                'title' => ($isRemote ? '📹 ' : '').($appointment->service->name ?? 'Servicio Desconocido'), // Ahora el título es el servicio
                'start' => $appointment->start_time->toIso8601String(), // Use ISO 8601 format
                'end' => $appointment->end_time->toIso8601String(),
                'color' => $color,
                'borderColor' => $isRemote ? '#7c3aed' : $color, // violeta = remota
                // Add any other custom properties you might want in the eventClick popup
                'extendedProps' => [
                    'service' => $appointment->service->name ?? 'N/A',
                    'clientName' => $appointment->client_first_name.' '.$appointment->client_last_name, // Nombre del cliente
                    'clientEmail' => $appointment->client_email,
                    'clientPhone' => $appointment->client_phone,
                    'status' => $appointment->status,
                    'notes' => $appointment->notes,
                    'address' => $appointment->address,
                    'issue' => $appointment->issue_description,
                    'equipmentPhotoPath' => $appointment->equipment_photo_path,
                    'equipmentPhotoUrl' => $appointment->equipment_photo_url,
                    // Datos propios de la modalidad remota
                    'modality' => $appointment->modality,
                    'isRemote' => $isRemote,
                    'paymentStatus' => $appointment->payment_status,
                    'paymentReference' => $appointment->payment_reference,
                    'paymentAmount' => $appointment->payment_amount,
                    'paymentCurrency' => $appointment->payment_currency,
                    'payerName' => $appointment->payer_name,
                    'clientTimezone' => $appointment->client_timezone,
                    // El enlace solo viaja a un endpoint autenticado de admin.
                    'meetingUrl' => $appointment->meeting_url,
                    'meetingLinkFailed' => $appointment->meeting_link_failed_at !== null,
                    // Add more details as needed
                ],
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

                    // Ensure the duration hasn't accidentally changed (sanity check).
                    // Carbon 3 returns a signed float here, so force absolute and truncate
                    // to whole minutes to keep the comparison against the service duration.
                    if ((int) $newStartTime->diffInMinutes($newEndTime, true) != $serviceDuration) {
                        // Use a more specific exception maybe, or just RuntimeException
                        throw new \RuntimeException('La duración de la cita no puede cambiar.', 422);
                    }

                    // Check for conflicts with other appointments (excluding the current one)
                    $existingAppointment = Appointment::where('id', '!=', $appointment->id)
                        ->where(function ($query) use ($newStartTime, $newEndTime) {
                            $query->where('start_time', '<', $newEndTime)
                                ->where('end_time', '>', $newStartTime);
                        })
                        // Consider only relevant statuses for conflict checking
                        // Use constants if available and appropriate (e.g., Appointment::STATUS_NEW)
                        ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
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
                        $companyData = CompanyData::first();

                        if (! $companyData) {
                            // Si no hay datos de la empresa, registramos el error pero igualmente actualizamos el estado
                            \Log::error('No company data found for sending rescheduling email notification');

                            return;
                        }

                        // Cargar las relaciones necesarias
                        $updatedAppointment->load(['service', 'brand']);

                        // Enviar correo de reagendamiento
                        Mail::to($updatedAppointment->client_email)
                            ->send(new AppointmentRescheduled($updatedAppointment, $companyData));

                        Log::info('Rescheduling email sent for appointment', ['id' => $updatedAppointment->id]);
                    } catch (\Exception $e) {
                        // Log the error but don't fail the response
                        \Log::error('Error sending rescheduling email notification: '.$e->getMessage());
                    }
                }
            );

            // If transaction is successful
            return response()->json(['message' => 'Cita actualizada correctamente. Se ha enviado un correo de notificación al cliente.']);

        } catch (\RuntimeException $re) {
            // Catch specific conflict/duration errors thrown from within the transaction
            Log::warning('Business logic error during calendar update: '.$re->getMessage(), [
                'id' => $appointment->id,
                'code' => $re->getCode(),
            ]);

            return response()->json(['message' => $re->getMessage()], $re->getCode() ?: 422);
        } catch (Throwable $e) {
            // Catch any other errors during the transaction
            Log::error('Error during calendar appointment update transaction: '.$e->getMessage(), [
                'id' => $appointment->id,
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Error al actualizar la cita en el calendario.'], 500);
        }
    }

    /**
     * Update the specified appointment's status.
     *
     * @param  int  $id
     * @return Response
     */
    public function updateStatus(Request $request, $id)
    {
        // Validate request data
        $request->validate([
            'status' => 'required|in:Confirmed,Cancelled',
        ]);

        // Find the appointment
        $appointment = Appointment::findOrFail($id);

        // Las remotas con pago sin verificar deben pasar por verify-payment (FR-3).
        if ($request->status === 'Confirmed' && $appointment->isRemote()) {
            if ($appointment->payment_status !== Appointment::PAYMENT_VERIFIED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las citas remotas requieren verificar el pago antes de confirmar. Usa "Confirmar pago y enviar enlace".',
                ], 422);
            }
        }

        // Las remotas pendientes de pago deben rechazarse vía verify-payment.
        if ($request->status === 'Cancelled'
            && $appointment->isRemote()
            && $appointment->payment_status === Appointment::PAYMENT_CLAIMED) {
            return response()->json([
                'success' => false,
                'message' => 'Para rechazar el pago de una cita remota usa "Rechazar pago" en el modal.',
            ], 422);
        }

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

        // US-5: si se cancela una cita remota cuyo pago YA estaba verificado, el
        // dinero ya entró y hay que devolverlo. No se reembolsa automáticamente
        // (Cesar lo tramita en SumUp a mano, fuera de alcance), pero SÍ queda
        // registrado como reembolso pendiente para que no se pierda de vista.
        // Guardado por isRemote(): el flujo presencial no se altera.
        $refundPending = false;
        if ($request->status === 'Cancelled'
            && $appointment->isRemote()
            && $appointment->payment_status === Appointment::PAYMENT_VERIFIED) {
            $appointment->payment_status = Appointment::PAYMENT_REFUND_PENDING;
            $refundPending = true;
            $this->paymentEvents->log(
                $appointment,
                AppointmentPaymentEvent::TYPE_REFUND_PENDING,
                $request->user(),
            );
        }

        $appointment->save();

        // Send email notification based on the new status
        try {
            // Obtener los datos de la empresa
            $companyData = CompanyData::first();

            if (! $companyData) {
                // Si no hay datos de la empresa, registramos el error pero igualmente actualizamos el estado
                \Log::error('No company data found for sending email notification');
                $message = 'Estado actualizado, pero no se pudo enviar el correo por falta de datos de la empresa.';
            } else {
                // Cargar las relaciones necesarias
                $appointment->load(['service', 'brand']);

                if ($request->status === 'Confirmed') {
                    Mail::to($appointment->client_email)->send(new AppointmentConfirmed($appointment, $companyData));
                    $message = 'Cita confirmada exitosamente. Se ha enviado un correo de confirmación al cliente.';
                } elseif ($request->status === 'Cancelled') {
                    // Una cancelación remota lleva su propio email: menciona el
                    // reembolso si lo hay y no habla de un técnico que iba a ir.
                    if ($appointment->isRemote()) {
                        Mail::to($appointment->client_email)
                            ->send(new RemoteAssistanceCancelled($appointment, $companyData, $refundPending));
                    } else {
                        Mail::to($appointment->client_email)->send(new AppointmentCancelled($appointment, $companyData));
                    }
                    $message = $refundPending
                        ? 'Cita cancelada. Queda registrado un reembolso pendiente de tramitar en SumUp.'
                        : 'Cita cancelada. Se ha enviado un correo de notificación al cliente.';
                }
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the response
            \Log::error('Error sending email notification: '.$e->getMessage());
            $message = 'Estado actualizado, pero hubo un problema al enviar el correo de notificación.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'id' => $appointment->id,
                'old_status' => $oldStatus,
                'new_status' => $appointment->status,
                'refund_pending' => $refundPending,
                'payment_status' => $appointment->payment_status,
            ],
        ]);
    }
}
