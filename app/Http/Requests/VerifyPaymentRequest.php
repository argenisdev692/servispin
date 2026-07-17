<?php

namespace App\Http\Requests;

use App\Services\MeetingLink\MeetingLinkProvider;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Verificación manual del pago de una cita remota (US-2).
 *
 * Este es el endpoint que decide si alguien recibe o no una videollamada que
 * cuesta dinero. Con el flujo por QR no hay webhook ni conciliación automática
 * (research #2): el criterio humano de Cesar cotejando SumUp es el ÚNICO control
 * de pago que existe en todo el sistema.
 */
class VerifyPaymentRequest extends FormRequest
{
    /**
     * Solo un administrador puede dejar pasar una llamada (US-2).
     *
     * ⚠️ El guard se pasa EXPLÍCITAMENTE, y no es un capricho: los roles y
     * permisos están sembrados con guard 'sanctum' (DatabaseSeeder), pero
     * config/auth.php declara 'defaults.guard' => 'web'. Spatie resuelve el
     * guard por defecto del modelo User —que sale 'web'— y buscaría un rol
     * 'Admin' con guard 'web' que no existe: devolvería false para TODO el
     * mundo, Cesar incluido. Pasando 'sanctum' a mano casa con lo sembrado.
     *
     * Por el mismo motivo NO se usa el middleware `role:`/`permission:` en la
     * ruta: sufriría exactamente el mismo desajuste, y en silencio.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasRole('Admin', 'sanctum');
    }

    public function rules(): array
    {
        return [
            'decision' => 'required|in:verify,reject',

            // Con un proveedor manual no hay nadie que genere el enlace: si Cesar
            // confirma sin pegarlo, el cliente recibiría un email de "confirmada"
            // sin forma de entrar a la llamada.
            'meeting_url' => [
                $this->requiresManualLink() ? 'required' : 'nullable',
                'url',
                'max:2048',
            ],

            'reason' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'meeting_url.required' => 'El proveedor de enlaces está en modo manual: pega el enlace de la videollamada antes de confirmar.',
            'decision.in' => 'La decisión debe ser "verify" o "reject".',
        ];
    }

    private function requiresManualLink(): bool
    {
        return $this->input('decision') === 'verify'
            && ! app(MeetingLinkProvider::class)->isAutomatic();
    }
}
