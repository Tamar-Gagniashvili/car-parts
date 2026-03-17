<?php

namespace Database\Factories;

use App\Models\ProductVehicleFit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVehicleFit>
 */
class ProductVehicleFitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $yearFrom = $this->faker->numberBetween(1998, 2018);
        $yearTo = $this->faker->boolean(80) ? $this->faker->numberBetween($yearFrom, min($yearFrom + 10, 2024)) : null;

        return [
            'product_id' => null, // set by seeder
            'manufacturer_external_id' => $this->faker->numberBetween(1, 500),
            'model_external_id' => $this->faker->numberBetween(1, 5000),
            'year_from' => $yearFrom,
            'year_to' => $yearTo,
            'volume' => $this->faker->optional(0.6)->randomElement(['1.4', '1.6', '1.8', '2.0', '2.2', '2.5', '3.0']),
            'is_main' => false,
        ];
    }
}
