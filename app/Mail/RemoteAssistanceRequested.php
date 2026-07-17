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
 * "Hemos recibido tu solicitud" (US-1).
 *
 * ⚠️ ESTE EMAIL NO PUEDE CONTENER EL ENLACE DE LA VIDEOLLAMADA (FR-3).
 *
 * No es un detalle de redacción: con el flujo por QR, el sistema no puede
 * comprobar el pago por sí mismo (research #2). Si este email llevara el enlace,
 * cualquiera podría escribir una referencia de pago inventada y recibir una
 * videollamada gratis. La única barrera que existe es que Cesar coteje el cobro
 * en SumUp antes de que se envíe el enlace.
 *
 * Hay un test que falla si este email llega a contener meeting_url. No lo borres.
 */
class RemoteAssistanceRequested extends Mailable
{
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

    public function envelope()
    {
        return new Envelope(
            from: new Address($this->companyData->email, $this->companyData->company_name),
            subject: $this->isForCompany
                ? 'Pago por verificar — asistencia remota de '.$this->appointment->client_first_name.' '.$this->appointment->client_last_name
                : 'Hemos recibido tu solicitud de asistencia remota',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.remote-assistance.requested',
        );
    }

    public function attachments()
    {
        return [];
    }
}
