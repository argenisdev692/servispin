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
 * Cancelación de una cita remota (US-5).
 *
 * Si la cita estaba pagada, el email confirma que el reembolso está en marcha —
 * el cliente ha pagado por adelantado y tiene derecho a saber que va a recuperar
 * su dinero. El reembolso lo tramita Cesar en SumUp a mano (fuera de alcance);
 * aquí solo se le comunica.
 */
class RemoteAssistanceCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;

    public $companyData;

    public $refundPending;

    public function __construct(Appointment $appointment, CompanyData $companyData, bool $refundPending = false)
    {
        $this->appointment = $appointment;
        $this->companyData = $companyData;
        $this->refundPending = $refundPending;
    }

    public function envelope()
    {
        return new Envelope(
            from: new Address($this->companyData->email, $this->companyData->company_name),
            subject: 'Tu cita de asistencia remota ha sido cancelada',
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
