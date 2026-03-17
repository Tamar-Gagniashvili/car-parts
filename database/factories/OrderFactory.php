<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SaleChannel;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => null, // set by seeder
            'order_number' => $this->faker->unique()->bothify('ORD-########'),
            'status' => $this->faker->randomElement([OrderStatus::Confirmed, OrderStatus::Completed])->value,
            'payment_status' => $this->faker->randomElement([PaymentStatus::Paid, PaymentStatus::Unpaid, PaymentStatus::Partial])->value,
            'sale_channel' => $this->faker->randomElement([SaleChannel::Internal, SaleChannel::MyParts])->value,
            'subtotal' => 0,
            'discount' => 0,
            'total' => 0,
            'notes' => $this->faker->optional(0.15)->sentence(),
            'sold_at' => $this->faker->optional(0.85)->dateTimeBetween('-45 days', 'now'),
        ];
    }
}
