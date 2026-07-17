<?php

namespace App\Mail;

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
    use Queueable, SerializesModels;

    /**
     * The appointment instance.
     *
     * @var Appointment
     */
    public $appointment;

    /**
     * The company data instance.
     *
     * @var CompanyData
     */
    public $companyData;

    /**
     * Whether this email is for the company or the client.
     *
     * @var bool
     */
    public $isForCompany;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Appointment $appointment, CompanyData $companyData, bool $isForCompany = false)
    {
        $this->appointment = $appointment;
        $this->companyData = $companyData;
        $this->isForCompany = $isForCompany;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address($this->companyData->email, $this->companyData->company_name),
            cc: $this->companyData->adminEmail() ? [new Address($this->companyData->adminEmail())] : [],
            subject: $this->isForCompany
                ? 'Nueva cita registrada: '.$this->appointment->client_first_name.' '.$this->appointment->client_last_name
                : 'Confirmación de su cita con '.$this->companyData->company_name,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.appointment-confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
