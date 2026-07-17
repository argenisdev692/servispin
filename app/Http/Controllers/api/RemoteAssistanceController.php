<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRemoteAssistanceRequest;
use App\Mail\RemoteAssistanceRequested;
use App\Models\Appointment;
use App\Models\Brand;
use App\Models\CompanyData;
use App\Models\Service;
use App\Services\SchedulingService;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Flujo público de asistencia técnica remota (US-1, US-4).
 *
 * Anónimo y sin cuenta (FR-1). El cliente paga por el QR de SumUp, declara el
 * pago aquí, y NO recibe enlace hasta que Cesar coteja el cobro (FR-3): con el
 * flujo por QR el sistema no puede verificar nada por sí mismo (research #2), así
 * que la verificación humana no es una simplificación provisional — es el único
 * control de pago que existe.
 */
class RemoteAssistanceController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
        protected SchedulingService $scheduling,
    ) {}

    /**
     * Landing del servicio (US-4): anuncio, precio y duración antes de pedir nada.
     */
    public function landing()
    {
        $service = Service::remote()->active()->first();

        return view('remote-assistance.landing', [
            'service' => $service,
            'companyData' => CompanyData::first(),
        ]);
    }

    /**
     * Formulario de solicitud (US-1).
     */
    public function bookingForm()
    {
        $service = Service::remote()->active()->first();

        if (! $service) {
            abort(404, 'El servicio de asistencia remota no está disponible en este momento.');
        }

        return view('remote-assistance.book', [
            'service' => $service,
            'brands' => Brand::orderBy('name')->get(),
            'companyData' => CompanyData::first(),
            'businessTimezone' => config('remote_assistance.business_timezone', 'Atlantic/Canary'),
        ]);
    }

    /**
     * Registra la solicitud con el pago declarado pero SIN verificar (US-1).
     */
    public function store(StoreRemoteAssistanceRequest $request)
    {
        $data = $request->validated();
        $service = $request->service();

        // El instante se persiste en el huso de la aplicación, igual que las
        // citas presenciales. NO en UTC: mezclar convenciones en start_time
        // rompería la detección de solapamiento en silencio (plan §9 R-5).
        // El huso del cliente viaja aparte, en client_timezone, y solo se usa
        // para mostrarle SU hora.
        $startTime = Carbon::parse($data['start_time']);
        $endTime = $startTime->copy()->addMinutes($service->duration);

        // FR-7: la agenda del técnico es una sola. Una videollamada y una visita
        // presencial no pueden ocupar el mismo hueco.
        if ($conflict = $this->scheduling->conflictFor($startTime, $endTime)) {
            Log::warning('Conflicto de horario en solicitud remota', [
                'requested_start' => $startTime->toDateTimeString(),
                'conflicting_appointment_id' => $conflict->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ese horario acaba de ocuparse. Por favor, elige otro hueco.',
            ], 422);
        }

        $photoPath = null;
        if ($request->hasFile('equipment_photo') && $request->file('equipment_photo')->isValid()) {
            try {
                $photoPath = ImageHelper::storeAndResizeLocally($request->file('equipment_photo'), 'appointment_photos', 'supabase');
            } catch (Throwable $uploadError) {
                Log::error('Error procesando la foto de la solicitud remota: '.$uploadError->getMessage());

                return response()->json(['success' => false, 'message' => 'Error al procesar la foto.'], 500);
            }
        }

        try {
            $appointment = $this->transactionService->run(
                function () use ($data, $startTime, $endTime, $photoPath) {
                    // El uuid lo genera Appointment::boot(); no se pasa aquí.
                    return Appointment::create([
                        'service_id' => $data['service_id'],
                        'brand_id' => $data['brand_id'],
                        'client_first_name' => ucfirst(strtolower($data['client_first_name'])),
                        'client_last_name' => ucfirst(strtolower($data['client_last_name'])),
                        'client_email' => $data['client_email'],
                        'client_phone' => $data['client_phone'],
                        'notes' => $data['notes'] ?? null,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'issue_description' => $data['issue_description'],
                        'equipment_photo_path' => $photoPath,

                        // FR-11: sin dirección. Nadie se desplaza.
                        'address' => null,

                        'status' => Appointment::STATUS_PENDING,
                        'modality' => Appointment::MODALITY_REMOTE,
                        'client_timezone' => $data['client_timezone'],

                        // El cliente DICE que pagó. Nadie lo ha comprobado aún.
                        'payment_status' => Appointment::PAYMENT_CLAIMED,
                        'payment_reference' => $data['payment_reference'],
                        'payment_amount' => $data['payment_amount'],
                        'payment_currency' => 'EUR',
                        'payer_name' => $data['payer_name'],
                        'payment_claimed_at' => now(),

                        // FR-3: sin enlace. No existe forma de generarlo aquí.
                        'meeting_url' => null,
                    ]);
                },
                function (Appointment $appointment) {
                    $this->sendRequestedEmail($appointment);
                },
                function (Throwable $dbError) use ($photoPath) {
                    if ($photoPath && Storage::disk('supabase')->exists($photoPath)) {
                        Storage::disk('supabase')->delete($photoPath);
                        Log::warning('Foto revertida de Supabase tras fallo de BD: '.$photoPath);
                    }
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Solicitud recibida. Comprobaremos tu pago y te enviaremos el enlace de la videollamada por email. '
                    .'Tu cita no es firme hasta que recibas ese email.',
                // FR-3: la respuesta NUNCA lleva meeting_url. No es que aún no
                // exista: es que este endpoint no puede devolverlo jamás.
                'data' => [
                    'uuid' => $appointment->uuid,
                    'start_time' => $appointment->start_time->toDateTimeString(),
                    'client_timezone' => $appointment->client_timezone,
                    'status' => $appointment->status,
                ],
            ], 201);

        } catch (Throwable $e) {
            Log::error('Error creando la solicitud de asistencia remota: '.$e->getMessage(), ['exception' => $e]);

            if ($photoPath && Storage::disk('supabase')->exists($photoPath)) {
                Storage::disk('supabase')->delete($photoPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la solicitud. Por favor, inténtalo de nuevo.',
            ], 500);
        }
    }

    /**
     * Email de "solicitud recibida" — SIN enlace y avisando de que la cita no es
     * firme (FR-3, US-1). Va en onCommit: si Resend falla, la cita no se pierde.
     */
    private function sendRequestedEmail(Appointment $appointment): void
    {
        try {
            $companyData = CompanyData::first();

            if (! $companyData) {
                Log::error('No hay company data para enviar el email de solicitud remota', [
                    'appointment_id' => $appointment->id,
                ]);

                return;
            }

            $appointment->load(['service', 'brand']);

            Mail::to($appointment->client_email)
                ->send(new RemoteAssistanceRequested($appointment, $companyData));

            // Aviso a Servispin: hay un pago que cotejar en SumUp.
            if ($companyData->email) {
                Mail::to($companyData->email)
                    ->send(new RemoteAssistanceRequested($appointment, $companyData, true));
            }
        } catch (Throwable $e) {
            Log::error('Error enviando el email de solicitud remota', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
