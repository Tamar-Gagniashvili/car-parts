<?php

namespace Database\Factories;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => null, // set by seeder
            'type' => $this->faker->randomElement(InventoryMovementType::cases())->value,
            'quantity' => $this->faker->numberBetween(1, 5),
            'reference_type' => null,
            'reference_id' => null,
            'note' => $this->faker->optional(0.2)->sentence(),
            'created_by' => null,
        ];
    }
}
