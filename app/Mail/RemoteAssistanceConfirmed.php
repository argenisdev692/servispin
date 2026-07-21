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
 * "Tu cita está confirmada, aquí tienes el enlace" (US-2).
 *
 * Este es el ÚNICO email del módulo que puede llevar el enlace, y solo se envía
 * después de que un administrador haya cotejado el pago en SumUp (FR-3).
 */
class RemoteAssistanceConfirmed extends Mailable
{
    use NotifiesAppointmentParties;
    use Queueable, SerializesModels;

    public $appointment;

    public $companyData;

    public $isForTechnician;

    public function __construct(Appointment $appointment, CompanyData $companyData, bool $isForTechnician = false)
    {
        $this->appointment = $appointment;
        $this->companyData = $companyData;
        $this->isForTechnician = $isForTechnician;
    }

    public static function notifyParties(Appointment $appointment, CompanyData $companyData): void
    {
        static::dispatchToParties(
            $appointment,
            $companyData,
            fn (bool $isForCompany) => new static($appointment, $companyData, $isForCompany)
        );
    }

    public function envelope()
    {
        return new Envelope(
            from: new Address($this->companyData->email, $this->companyData->company_name),
            cc: $this->operationalCc(),
            subject: $this->isForTechnician
                ? 'Videollamada confirmada: '.$this->appointment->client_first_name.' '.$this->appointment->client_last_name
                : 'Tu cita está confirmada — enlace de la videollamada',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.remote-assistance.confirmed',
        );
    }

    public function attachments()
    {
        return [];
    }
}
