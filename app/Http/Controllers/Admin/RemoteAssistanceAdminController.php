<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRemoteAppointmentRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Mail\RemoteAssistanceConfirmed;
use App\Mail\RemoteAssistanceRejected;
use App\Models\Appointment;
use App\Models\CompanyData;
use App\Models\Service;
use App\Services\MeetingLink\MeetingLinkException;
use App\Services\MeetingLink\MeetingLinkProvider;
use App\Services\SchedulingService;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Gestión de citas remotas por parte de Cesar (US-2, US-6).
 *
 * Aquí vive el único control de pago del sistema: con el QR de SumUp nadie nos
 * avisa de si el cobro entró (research #2), así que hasta que un humano lo
 * coteja, la cita no se confirma y el enlace no existe (FR-3).
 */
class RemoteAssistanceAdminController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
        protected SchedulingService $scheduling,
        protected MeetingLinkProvider $meetingLinks,
    ) {}

    /**
     * Bandeja de verificación: las solicitudes con pago declarado sin cotejar (US-2).
     */
    public function index(Request $request)
    {
        $appointments = Appointment::with(['service', 'brand'])
            ->pendingPaymentVerification()
            ->orderBy('start_time')
            ->paginate(20);

        // Citas que se quedaron sin enlace porque el provider automático falló:
        // están confirmadas y pagadas, así que son urgentes (FR-15).
        $awaitingLink = Appointment::with('service')->awaitingManualLink()->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $appointments,
                'awaiting_link' => $awaitingLink,
            ]);
        }

        return view('admin.remote-assistance.index', [
            'appointments' => $appointments,
            'awaitingLink' => $awaitingLink,
            'providerIsAutomatic' => $this->meetingLinks->isAutomatic(),
        ]);
    }

    /**
     * Confirma o rechaza una solicitud tras cotejar el pago en SumUp (US-2).
     */
    public function verifyPayment(VerifyPaymentRequest $request, $id)
    {
        $appointment = Appointment::find($id);

        if (! $appointment) {
            return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
        }

        if (! $appointment->isRemote()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta cita no es remota: no tiene pago que verificar.',
            ], 422);
        }

        if ($appointment->payment_status === Appointment::PAYMENT_VERIFIED) {
            return response()->json([
                'success' => false,
                'message' => 'El pago de esta cita ya fue verificado.',
            ], 422);
        }

        return $request->validated()['decision'] === 'verify'
            ? $this->confirmPaidAppointment($appointment, $request)
            : $this->rejectAppointment($appointment, $request);
    }

    /**
     * El pago apareció en SumUp: se confirma la cita y se emite el enlace.
     */
    private function confirmPaidAppointment(Appointment $appointment, VerifyPaymentRequest $request)
    {
        $data = $request->validated();
        $verifier = $request->user();

        try {
            $result = $this->transactionService->run(
                function () use ($appointment, $data, $verifier) {
                    // El enlace manual llega en la petición; el automático lo
                    // genera el provider a partir de la cita ya poblada.
                    if (! empty($data['meeting_url'])) {
                        $appointment->meeting_url = $data['meeting_url'];
                    }

                    $appointment->payment_status = Appointment::PAYMENT_VERIFIED;
                    $appointment->payment_verified_at = now();
                    $appointment->payment_verified_by = $verifier->id; // FR-5
                    $appointment->status = Appointment::STATUS_CONFIRMED;

                    $this->resolveMeetingLink($appointment);

                    $appointment->save();

                    return $appointment;
                },
                function (Appointment $confirmed) {
                    $this->sendConfirmedEmail($confirmed);
                }
            );

            return response()->json([
                'success' => true,
                'message' => $result->meeting_url
                    ? 'Cita confirmada y enlace enviado al cliente.'
                    : 'Cita confirmada, pero no se pudo generar el enlace: añádelo a mano y reenvía el email.',
                'data' => [
                    'status' => $result->status,
                    'payment_status' => $result->payment_status,
                    'meeting_url' => $result->meeting_url,
                    'meeting_link_failed' => $result->meeting_link_failed_at !== null,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Error verificando el pago de la cita remota: '.$e->getMessage(), [
                'appointment_id' => $appointment->id,
                'exception' => $e,
            ]);

            return response()->json(['success' => false, 'message' => 'Error al confirmar la cita.'], 500);
        }
    }

    /**
     * Resuelve el enlace SIN poder tumbar la confirmación (FR-15, plan §3).
     *
     * Este método es el motivo por el que MeetingLinkProvider es una interfaz.
     * El refresh token de Google se revoca a los 7 días si la app está en
     * "Testing" (research #7b), y ese fallo aparecería en producción días
     * después de dar el módulo por terminado. Si Google se cae, el dinero ya
     * entró: la cita se confirma igual, se marca, y Cesar pega el enlace.
     * Un fallo de Google no puede costar una cita cobrada.
     */
    private function resolveMeetingLink(Appointment $appointment): void
    {
        try {
            $link = $this->meetingLinks->linkFor($appointment);

            if ($link) {
                $appointment->meeting_url = $link;
                $appointment->meeting_provider = $this->meetingLinks->name();
                $appointment->meeting_link_failed_at = null;

                return;
            }

            // Sin enlace y sin excepción: el provider manual todavía no lo tiene.
            if (! $appointment->meeting_url) {
                $appointment->meeting_link_failed_at = now();
            }
        } catch (MeetingLinkException|Throwable $e) {
            Log::error('El proveedor de enlaces falló; la cita se confirma igualmente (FR-15).', [
                'appointment_id' => $appointment->id,
                'provider' => $this->meetingLinks->name(),
                'error' => $e->getMessage(),
            ]);

            $appointment->meeting_link_failed_at = now();
            $appointment->meeting_url = $appointment->meeting_url ?: null;
        }
    }

    /**
     * El pago no apareció en SumUp: se cancela y se libera el hueco.
     */
    private function rejectAppointment(Appointment $appointment, VerifyPaymentRequest $request)
    {
        $data = $request->validated();
        $verifier = $request->user();

        try {
            $result = $this->transactionService->run(
                function () use ($appointment, $data, $verifier) {
                    $appointment->payment_status = Appointment::PAYMENT_REJECTED;
                    $appointment->payment_verified_at = now();
                    $appointment->payment_verified_by = $verifier->id; // FR-5
                    // Cancelada ⇒ deja de contar para el solapamiento: el hueco
                    // vuelve a estar libre sin tener que tocar nada más.
                    $appointment->status = Appointment::STATUS_CANCELLED;
                    $appointment->notes = trim(($appointment->notes ?? '')."\nRechazo: ".($data['reason'] ?? 'pago no localizado'));
                    $appointment->save();

                    return $appointment;
                },
                function (Appointment $rejected) use ($data) {
                    $this->sendRejectedEmail($rejected, $data['reason'] ?? null);
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada y hueco liberado. Se ha avisado al cliente.',
                'data' => [
                    'status' => $result->status,
                    'payment_status' => $result->payment_status,
                    'meeting_url' => null,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Error rechazando la solicitud remota: '.$e->getMessage(), ['appointment_id' => $appointment->id]);

            return response()->json(['success' => false, 'message' => 'Error al rechazar la solicitud.'], 500);
        }
    }

    /**
     * Alta de cita remota desde el calendario del admin (US-6 / FR-13).
     *
     * El cliente llama por teléfono, paga por QR y Cesar la da de alta él mismo.
     */
    public function store(StoreAdminRemoteAppointmentRequest $request)
    {
        $data = $request->validated();
        $service = Service::findOrFail($data['service_id']);
        $verifier = $request->user();

        $startTime = Carbon::parse($data['start_time']);
        $endTime = $startTime->copy()->addMinutes($service->duration);

        // FR-7: aplica igual que en el flujo público. El atajo del admin no
        // puede meter dos citas en el mismo hueco.
        if ($this->scheduling->hasConflict($startTime, $endTime)) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una cita en ese horario.',
            ], 422);
        }

        $paymentVerified = (bool) ($data['payment_verified'] ?? false);

        try {
            $appointment = $this->transactionService->run(
                function () use ($data, $startTime, $endTime, $paymentVerified, $verifier) {
                    $appointment = new Appointment([
                        'service_id' => $data['service_id'],
                        'brand_id' => $data['brand_id'] ?? null,
                        'client_first_name' => ucfirst(strtolower($data['client_first_name'])),
                        'client_last_name' => ucfirst(strtolower($data['client_last_name'])),
                        'client_email' => $data['client_email'],
                        'client_phone' => $data['client_phone'] ?? null,
                        'notes' => $data['notes'] ?? null,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'issue_description' => $data['issue_description'] ?? null,
                        'address' => null, // FR-11
                        'modality' => Appointment::MODALITY_REMOTE,
                        'client_timezone' => $data['client_timezone'],
                        'payment_reference' => $data['payment_reference'] ?? null,
                        'payment_amount' => $data['payment_amount'] ?? null,
                        'payment_currency' => 'EUR',
                        'payer_name' => $data['payer_name'] ?? null,
                        'payment_claimed_at' => now(),
                    ]);

                    if ($paymentVerified) {
                        // Cesar dice que ya cobró: se sella quién lo verificó (FR-5)
                        // y la cita nace confirmada, con enlace, en un solo paso.
                        $appointment->payment_status = Appointment::PAYMENT_VERIFIED;
                        $appointment->payment_verified_at = now();
                        $appointment->payment_verified_by = $verifier->id;
                        $appointment->status = Appointment::STATUS_CONFIRMED;
                        $appointment->meeting_url = $data['meeting_url'] ?? null;

                        $this->resolveMeetingLink($appointment);
                    } else {
                        // FR-3 aplica IGUAL que en el formulario público: el atajo
                        // del admin no es una puerta trasera al control de pago.
                        $appointment->payment_status = Appointment::PAYMENT_CLAIMED;
                        $appointment->status = Appointment::STATUS_PENDING;
                        $appointment->meeting_url = null;
                    }

                    $appointment->save();

                    return $appointment;
                },
                function (Appointment $created) use ($paymentVerified) {
                    if ($paymentVerified) {
                        $this->sendConfirmedEmail($created);
                    }
                }
            );

            return response()->json([
                'success' => true,
                'message' => $paymentVerified
                    ? 'Cita remota creada y confirmada. Se ha enviado el enlace al cliente.'
                    : 'Cita remota creada, pendiente de verificar el pago. No se ha enviado enlace.',
                'data' => [
                    'uuid' => $appointment->uuid,
                    'status' => $appointment->status,
                    'payment_status' => $appointment->payment_status,
                    'meeting_url' => $appointment->meeting_url,
                ],
            ], 201);
        } catch (Throwable $e) {
            Log::error('Error creando la cita remota desde el admin: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['success' => false, 'message' => 'Error al crear la cita.'], 500);
        }
    }

    private function sendConfirmedEmail(Appointment $appointment): void
    {
        try {
            $companyData = CompanyData::first();

            if (! $companyData) {
                Log::error('No hay company data para el email de confirmación remota', ['appointment_id' => $appointment->id]);

                return;
            }

            $appointment->load(['service', 'brand']);

            Mail::to($appointment->client_email)
                ->send(new RemoteAssistanceConfirmed($appointment, $companyData));

            // El técnico también recibe el enlace: es quien atiende la llamada.
            if ($companyData->email) {
                Mail::to($companyData->email)
                    ->send(new RemoteAssistanceConfirmed($appointment, $companyData, true));
            }
        } catch (Throwable $e) {
            Log::error('Error enviando el email de confirmación remota', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendRejectedEmail(Appointment $appointment, ?string $reason): void
    {
        try {
            $companyData = CompanyData::first();

            if (! $companyData) {
                return;
            }

            $appointment->load('service');

            Mail::to($appointment->client_email)
                ->send(new RemoteAssistanceRejected($appointment, $companyData, $reason));
        } catch (Throwable $e) {
            Log::error('Error enviando el email de rechazo remoto', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
