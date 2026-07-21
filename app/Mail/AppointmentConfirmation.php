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

class AppointmentConfirmation extends Mailable
{
    use NotifiesAppointmentParties;
    use Queueable, SerializesModels;

    public $appointment;

    public $companyData;

    public $isForCompany;

    public function __construct(Appointment $appointment, CompanyData $companyData, bool $isForCompany = false)
    {
        $this->appointment = $appointment;
        $this->companyData = $companyData;
        $this->isForCompany = $isForCompany;
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
            subject: $this->isForCompany
                ? 'Nueva cita registrada: '.$this->appointment->client_first_name.' '.$this->appointment->client_last_name
                : 'Confirmación de su cita con '.$this->companyData->company_name,
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.appointment-confirmation',
        );
    }

    public function attachments()
    {
        return [];
    }
}
