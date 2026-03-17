<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 15, 1200);
        $qty = $this->faker->numberBetween(1, 3);

        return [
            'order_id' => null, // set by seeder
            'product_id' => null, // set by seeder
            'product_name_snapshot' => $this->faker->words(asText: true),
            'unit_price' => $unitPrice,
            'quantity' => $qty,
            'total_price' => round($unitPrice * $qty, 2),
        ];
    }
}
