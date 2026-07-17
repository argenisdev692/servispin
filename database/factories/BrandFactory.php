<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        return [
            // El nombre es unique en la tabla; unique()->company() evita choques
            // cuando un test crea varias marcas.
            'name' => $this->faker->unique()->company(),
        ];
    }
}
