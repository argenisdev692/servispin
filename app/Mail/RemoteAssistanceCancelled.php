<?php

namespace App\Mail;

use App\Mail\Concerns\NotifiesAppointmentParties;
use App\Models\Appointment;
use App\Models\CompanyData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Cancelación de una cita remota (US-5).
 *
 * Destinatarios (mismo flujo que citas presenciales):
 *  - cliente · company_data.email · user admin · CC MAIL_CC_EMAIL
 */
class RemoteAssistanceCancelled extends Mailable
{
    use NotifiesAppointmentParties;
    use Queueable, SerializesModels;

    public $appointment;

    public $companyData;

    public $refundPending;

    public $isForTechnician;

    public function __construct(
        Appointment $appointment,
        CompanyData $companyData,
        bool $refundPending = false,
        bool $isForTechnician = false
    ) {
        $this->appointment = $appointment;
        $this->companyData = $companyData;
        $this->refundPending = $refundPending;
        $this->isForTechnician = $isForTechnician;
    }

    public static function notifyParties(
        Appointment $appointment,
        CompanyData $companyData,
        bool $refundPending = false
    ): void {
        static::dispatchToParties(
            $appointment,
            $companyData,
            fn (bool $isForCompany) => new static($appointment, $companyData, $refundPending, $isForCompany)
        );
    }

    public function envelope()
    {
        return new Envelope(
            from: new Address($this->companyData->email, $this->companyData->company_name),
            cc: $this->operationalCc(),
            subject: $this->isForTechnician
                ? 'Cita remota cancelada: '.$this->appointment->client_first_name.' '.$this->appointment->client_last_name
                : 'Tu cita de asistencia remota ha sido cancelada',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.remote-assistance.cancelled',
        );
    }

    public function attachments()
    {
        return [];
    }
}
