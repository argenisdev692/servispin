<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\CompanyData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class AppointmentCancelled extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The appointment instance.
     *
     * @var \App\Models\Appointment
     */
    public $appointment;

    /**
     * The company data instance.
     *
     * @var \App\Models\CompanyData
     */
    public $companyData;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Appointment  $appointment
     * @param  \App\Models\CompanyData  $companyData
     * @return void
     */
    public function __construct(Appointment $appointment, CompanyData $companyData)
    {
        $this->appointment = $appointment;
        $this->companyData = $companyData;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address($this->companyData->email, $this->companyData->company_name),
            subject: 'Su cita ha sido cancelada - ' . $this->companyData->company_name,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.appointment-cancelled',
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