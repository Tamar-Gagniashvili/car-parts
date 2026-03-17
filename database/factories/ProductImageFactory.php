<?php

namespace Database\Factories;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $seed = $this->faker->numberBetween(1, 9999);

        return [
            'product_id' => null, // set by seeder
            'thumb_url' => "https://picsum.photos/seed/part-thumb-{$seed}/240/240",
            'large_url' => "https://picsum.photos/seed/part-large-{$seed}/1200/800",
            'sort_order' => null,
        ];
    }
}
