<?php

namespace App\Http\Requests;

use App\Services\MeetingLink\MeetingLinkProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta de una cita remota por parte de Cesar (US-6 / FR-13).
 *
 * El cliente llama por teléfono y paga por QR sin pasar por la web. Los datos
 * del cliente se escriben a mano: no existe en el sistema y no hay lista de
 * donde elegirlo.
 */
class StoreAdminRemoteAppointmentRequest extends FormRequest
{
    /**
     * Mismo motivo que en VerifyPaymentRequest para pasar el guard explícito:
     * los roles están sembrados con guard 'sanctum' y config/auth.php declara
     * 'web' por defecto, así que sin el segundo argumento Spatie diría que no a
     * todo el mundo. Ver VerifyPaymentRequest::authorize().
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasRole('Admin', 'sanctum');
    }

    public function rules(): array
    {
        $rules = [
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where('is_remote', true),
            ],
            'brand_id' => 'nullable|exists:brands,id',

            // Escritos a mano: el cliente no existe en el sistema (US-6).
            'client_first_name' => 'required|string|max:120',
            'client_last_name' => 'required|string|max:135',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'nullable|string|max:25',
            'issue_description' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:2000',

            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'client_timezone' => 'required|timezone',

            // Si Cesar la marca como ya cobrada, queda registrado que fue él
            // quien lo verificó (FR-5).
            'payment_verified' => 'required|boolean',
            'payment_reference' => 'nullable|string|max:128',
            'payment_amount' => 'nullable|numeric|min:0|max:99999.99',
            'payer_name' => 'nullable|string|max:255',

            'meeting_url' => [
                $this->requiresManualLink() ? 'required' : 'nullable',
                'url',
                'max:2048',
            ],
        ];

        // FR-4: el guardarraíl de PCI vale igual aquí. Que la petición venga de
        // un admin autenticado no la saca del alcance regulatorio.
        foreach (StoreRemoteAssistanceRequest::PROHIBITED_PAYMENT_FIELDS as $field) {
            $rules[$field] = 'prohibited';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'meeting_url.required' => 'El proveedor está en modo manual: pega el enlace de la videollamada antes de crear la cita como pagada.',
            'service_id.exists' => 'El servicio seleccionado no es de asistencia remota.',
        ];
    }

    /**
     * Solo hace falta enlace a mano si la cita nace ya confirmada Y no hay quien
     * lo genere automáticamente. Si no está verificada, no lleva enlace en
     * absoluto (FR-3).
     */
    private function requiresManualLink(): bool
    {
        return $this->boolean('payment_verified')
            && ! app(MeetingLinkProvider::class)->isAutomatic();
    }
}
