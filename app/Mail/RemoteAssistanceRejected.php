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

/**
 * "No hemos localizado tu pago" (US-2).
 *
 * Sin enlace, obviamente, y con el motivo. El tono importa: lo más probable no
 * es que el cliente intentara colarse, sino que se equivocara al copiar la
 * referencia. El email tiene que dejarle una salida clara.
 */
class RemoteAssistanceRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;

    public $companyData;

    public $reason;

    public function __construct(Appointment $appointment, CompanyData $companyData, ?string $reason = null)
    {
        $this->appointment = $appointment;
        $this->companyData = $companyData;
        $this->reason = $reason;
    }

    public function envelope()
    {
        return new Envelope(
            from: new Address($this->companyData->email, $this->companyData->company_name),
            subject: 'No hemos podido confirmar tu asistencia remota',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.remote-assistance.rejected',
        );
    }

    public function attachments()
    {
        return [];
    }
}
