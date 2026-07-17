<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'duration' => 60,
            'price' => $this->faker->randomFloat(2, 0, 100),
            'active' => true,
            'is_remote' => false,
        ];
    }

    public function remote(): static
    {
        return $this->state(fn () => [
            'is_remote' => true,
            'duration' => 30,
            'price' => 30.00,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }
}
