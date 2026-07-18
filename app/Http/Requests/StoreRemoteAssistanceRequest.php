<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación de una solicitud pública de asistencia remota (US-1).
 *
 * Tres reglas que no son cosméticas:
 *  - FR-2: sin referencia de pago no hay solicitud. Es el único dato que permite
 *    a Cesar cotejar contra SumUp.
 *  - FR-4: NUNCA datos de tarjeta. Ver `$prohibitedPaymentFields`.
 *  - FR-11: no se pide dirección postal. Nadie se desplaza a ningún sitio.
 */
class StoreRemoteAssistanceRequest extends FormRequest
{
    /**
     * Campos que meterían a Servispin en alcance PCI-DSS si alguien los añadiera
     * "para verificar mejor el pago" (research #2, plan §8).
     *
     * El dato de tarjeta lo captura SumUp y nunca toca este servidor. Ese es el
     * único motivo por el que este módulo está fuera de PCI-DSS, y es una
     * propiedad que se pierde el día que uno solo de estos campos se acepte.
     * Por eso se rechazan de forma explícita y ruidosa, en vez de ignorarlos en
     * silencio: un 422 se ve, un campo ignorado no.
     */
    public const PROHIBITED_PAYMENT_FIELDS = [
        'card_number',
        'cardnumber',
        'card',
        'pan',
        'cvv',
        'cvc',
        'card_cvv',
        'card_expiry',
        'expiry',
        'expiration_date',
        'card_holder',
        'iban',
    ];

    /**
     * Formulario público y anónimo (FR-1): no hace falta cuenta.
     * La protección real es throttle + honeypot + el propio pago previo.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // Solo servicios marcados como remotos: el formulario remoto no puede
            // usarse para colar una reparación presencial sin dirección.
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where('is_remote', true)->where('active', true),
            ],
            'brand_id' => 'required|exists:brands,id',

            'client_first_name' => ['required', 'string', 'min:3', 'max:15', 'regex:/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+$/u'],
            'client_last_name' => ['required', 'string', 'min:3', 'max:15', 'regex:/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+$/u'],
            'client_email' => 'required|email|max:255',
            'client_phone' => 'required|string|max:25',
            'issue_description' => 'required|string|max:5000',

            'start_time' => 'required|date_format:Y-m-d H:i:s|after:now',

            // FR-6: sin el huso del cliente no podemos mostrarle su hora sin
            // ambigüedad, y R-5 dice que ese es el fallo más caro del módulo.
            'client_timezone' => 'required|timezone',

            // FR-2: declaración de pago. Cesar coteja esto contra la app de SumUp.
            'payment_reference' => 'required|string|max:128',
            'payment_amount' => 'required|numeric|min:0|max:99999.99',
            'payer_name' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[\p{L}]+(?: [\p{L}]+)*$/u'],

            'equipment_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'notes' => 'nullable|string|max:2000',

            // Honeypot: un bot rellena todo lo que ve; una persona no ve esto.
            'website_url' => 'prohibited',

            // FR-11: aquí NO se pide 'address' a propósito. No es un olvido.
        ];

        // FR-4 — guardarraíl activo: si alguien añade un campo de tarjeta al
        // formulario, esto lo rechaza en vez de dejarlo pasar sin más.
        foreach (self::PROHIBITED_PAYMENT_FIELDS as $field) {
            $rules[$field] = 'prohibited';
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'payment_reference.required' => 'La referencia del pago es obligatoria: sin ella no podemos comprobar que el cobro entró.',
            'payment_amount.required' => 'Indica el importe que has pagado.',
            'payer_name.required' => 'Indica el nombre con el que hiciste el pago.',
            'payer_name.min' => 'El nombre del pagador debe tener al menos 3 caracteres.',
            'payer_name.max' => 'El nombre del pagador no puede superar 20 caracteres.',
            'payer_name.regex' => 'El nombre del pagador solo puede contener letras y espacios.',
            'client_first_name.min' => 'El nombre debe tener al menos 3 letras.',
            'client_first_name.max' => 'El nombre no puede superar 15 caracteres.',
            'client_first_name.regex' => 'El nombre solo puede contener letras, sin espacios.',
            'client_last_name.min' => 'El apellido debe tener al menos 3 letras.',
            'client_last_name.max' => 'El apellido no puede superar 15 caracteres.',
            'client_last_name.regex' => 'El apellido solo puede contener letras, sin espacios.',
            'client_timezone.required' => 'No hemos podido determinar tu zona horaria. Selecciónala para confirmar la hora de tu cita.',
            'client_timezone.timezone' => 'La zona horaria indicada no es válida.',
            'service_id.exists' => 'El servicio seleccionado no está disponible para asistencia remota.',
            'website_url.prohibited' => 'Solicitud rechazada.',
        ];

        foreach (self::PROHIBITED_PAYMENT_FIELDS as $field) {
            $messages[$field.'.prohibited'] = 'No pedimos ni almacenamos datos de tarjeta. El pago se hace únicamente por el QR de SumUp.';
        }

        return $messages;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('service_id') || ! $this->filled('payment_amount')) {
                return;
            }

            $service = Service::find($this->input('service_id'));

            if ($service && round((float) $this->input('payment_amount'), 2) !== round((float) $service->price, 2)) {
                $validator->errors()->add(
                    'payment_amount',
                    'El importe debe ser '.number_format((float) $service->price, 2, '.', '').' €.'
                );
            }
        });
    }

    /**
     * El servicio remoto solicitado, ya validado.
     */
    public function service(): Service
    {
        return Service::findOrFail($this->validated()['service_id']);
    }
}
