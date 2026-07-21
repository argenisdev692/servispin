<?php

namespace App\Mail\Concerns;

use App\Models\Appointment;
use App\Models\CompanyData;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Mail;

/**
 * Destinatarios unificados para emails de citas (presenciales y flujo compartido):
 *  - cliente
 *  - company_data.email
 *  - user admin (company_data.user_id)
 *  - CC: config('mail.cc_email') / MAIL_CC_EMAIL
 */
trait NotifiesAppointmentParties
{
    /**
     * @return list<Address>
     */
    protected function operationalCc(): array
    {
        $ccEmail = trim((string) config('mail.cc_email', ''));

        return $ccEmail !== '' ? [new Address($ccEmail)] : [];
    }

    /**
     * @return list<string>
     */
    public static function internalEmails(CompanyData $companyData): array
    {
        $companyData->loadMissing('user');

        return collect([
            $companyData->email,
            $companyData->adminEmail(),
        ])
            ->filter()
            ->map(fn (string $email) => strtolower(trim($email)))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  callable(bool $isForCompany): object  $factory
     */
    protected static function dispatchToParties(
        Appointment $appointment,
        CompanyData $companyData,
        callable $factory
    ): void {
        $appointment->loadMissing(['service', 'brand']);
        $companyData->loadMissing('user');

        Mail::to($appointment->client_email)->send($factory(false));

        foreach (static::internalEmails($companyData) as $email) {
            Mail::to($email)->send($factory(true));
        }
    }
}
