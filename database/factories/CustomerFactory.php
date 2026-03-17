<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->optional(0.8)->numerify('+9955#######'),
            'email' => $this->faker->optional(0.5)->safeEmail(),
            'source' => $this->faker->optional(0.7)->randomElement(['Walk-in', 'Phone call', 'MyParts.ge', 'Facebook', 'Repeat customer']),
            'notes' => $this->faker->optional(0.2)->sentence(),
        ];
    }
}
