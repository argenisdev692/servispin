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
 * Recordatorio de una cita remota confirmada (US-3).
 */
class RemoteAssistanceReminder extends Mailable
{
    use NotifiesAppointmentParties;
    use Queueable, SerializesModels;

    public const WHEN_TOMORROW = 'tomorrow';

    public const WHEN_IMMINENT = 'imminent';

    public $appointment;

    public $companyData;

    public $when;

    public $isForTechnician;

    public function __construct(
        Appointment $appointment,
        CompanyData $companyData,
        string $when = self::WHEN_TOMORROW,
        bool $isForTechnician = false
    ) {
        $this->appointment = $appointment;
        $this->companyData = $companyData;
        $this->when = $when;
        $this->isForTechnician = $isForTechnician;
    }

    public static function notifyParties(
        Appointment $appointment,
        CompanyData $companyData,
        string $when = self::WHEN_TOMORROW
    ): void {
        static::dispatchToParties(
            $appointment,
            $companyData,
            fn (bool $isForCompany) => new static($appointment, $companyData, $when, $isForCompany)
        );
    }

    public function envelope()
    {
        $isImminent = $this->when === self::WHEN_IMMINENT;

        return new Envelope(
            from: new Address($this->companyData->email, $this->companyData->company_name),
            cc: $this->operationalCc(),
            subject: $isImminent
                ? 'Tu videollamada empieza en unos minutos'
                : 'Recordatorio: tu videollamada de mañana',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.remote-assistance.reminder',
        );
    }

    public function attachments()
    {
        return [];
    }
}
