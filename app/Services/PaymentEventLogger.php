<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentPaymentEvent;
use App\Models\User;

class PaymentEventLogger
{
    public function log(
        Appointment $appointment,
        string $eventType,
        ?User $user = null,
        ?string $notes = null,
    ): AppointmentPaymentEvent {
        return AppointmentPaymentEvent::create([
            'appointment_id' => $appointment->id,
            'event_type' => $eventType,
            'reference' => $appointment->payment_reference,
            'amount' => $appointment->payment_amount,
            'currency' => $appointment->payment_currency ?? 'EUR',
            'payer_name' => $appointment->payer_name,
            'recorded_by' => $user?->id,
            'notes' => $notes,
        ]);
    }
}
