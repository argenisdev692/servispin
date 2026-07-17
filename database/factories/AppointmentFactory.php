<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        // Las horas se generan en el huso de la aplicación (Atlantic/Canary),
        // igual que hace el flujo real. No se usa UTC a propósito: mezclar
        // convenciones en start_time rompería la detección de solapamiento
        // (plan §9 R-5).
        $start = now()->addDays(2)->setTime(10, 0);

        return [
            'service_id' => Service::factory(),
            'client_first_name' => $this->faker->firstName(),
            'client_last_name' => $this->faker->lastName(),
            'client_email' => $this->faker->safeEmail(),
            'client_phone' => '+34600000000',
            'start_time' => $start,
            'end_time' => (clone $start)->addMinutes(60),
            'issue_description' => $this->faker->sentence(),
            'status' => Appointment::STATUS_PENDING,
            'modality' => Appointment::MODALITY_ONSITE,
            'address' => $this->faker->address(),
            'payment_status' => Appointment::PAYMENT_UNPAID,
        ];
    }

    /**
     * Cita remota recién solicitada: el cliente dice que pagó, nadie lo ha
     * cotejado todavía y por tanto NO tiene enlace (FR-3).
     */
    public function remote(): static
    {
        return $this->state(fn () => [
            'modality' => Appointment::MODALITY_REMOTE,
            'address' => null,
            'client_timezone' => 'Europe/Madrid',
            'payment_status' => Appointment::PAYMENT_CLAIMED,
            'payment_reference' => 'SUMUP-'.$this->faker->bothify('####-????'),
            'payment_amount' => 30.00,
            'payment_currency' => 'EUR',
            'payer_name' => $this->faker->name(),
            'payment_claimed_at' => now(),
            'meeting_url' => null,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => Appointment::STATUS_CONFIRMED]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => Appointment::STATUS_CANCELLED]);
    }

    public function paymentVerified(): static
    {
        return $this->state(fn () => [
            'payment_status' => Appointment::PAYMENT_VERIFIED,
            'payment_verified_at' => now(),
        ]);
    }
}
