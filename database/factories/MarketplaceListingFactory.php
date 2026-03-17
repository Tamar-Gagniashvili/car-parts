<?php

namespace Database\Factories;

use App\Enums\MarketplaceChannel;
use App\Models\MarketplaceListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketplaceListing>
 */
class MarketplaceListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $externalId = (string) $this->faker->unique()->numberBetween(1000000, 9999999);

        return [
            'product_id' => null, // set by seeder
            'channel' => MarketplaceChannel::MyParts->value,
            'external_id' => $externalId,
            'external_status_id' => $this->faker->optional(0.8)->numberBetween(1, 6),
            'external_category_id' => $this->faker->optional(0.8)->numberBetween(10, 500),
            'external_category_title' => $this->faker->optional(0.7)->randomElement(['Engine parts', 'Suspension', 'Brakes', 'Electrical', 'Body parts']),
            'external_price' => $this->faker->optional(0.9)->randomFloat(2, 20, 1500),
            'external_currency_id' => $this->faker->optional(0.9)->randomElement([1, 2]),
            'external_quantity' => $this->faker->optional(0.8)->numberBetween(1, 10),
            'views' => $this->faker->optional(0.7)->numberBetween(0, 5000),
            'create_date' => $this->faker->optional(0.9)->dateTimeBetween('-120 days', '-10 days'),
            'update_date' => $this->faker->optional(0.9)->dateTimeBetween('-10 days', 'now'),
            'end_date' => $this->faker->optional(0.3)->dateTimeBetween('now', '+60 days'),
            'raw_payload' => null,
            'last_synced_at' => $this->faker->optional(0.9)->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
