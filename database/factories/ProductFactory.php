<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $make = $this->faker->randomElement(['Toyota', 'BMW', 'Mercedes-Benz', 'Volkswagen', 'Ford', 'Hyundai', 'Kia', 'Honda', 'Nissan', 'Audi']);
        $model = $this->faker->randomElement(match ($make) {
            'Toyota' => ['Corolla', 'Camry', 'RAV4', 'Prius'],
            'BMW' => ['3 Series', '5 Series', 'X5', 'X3'],
            'Mercedes-Benz' => ['C-Class', 'E-Class', 'GLA', 'GLC'],
            'Volkswagen' => ['Golf', 'Passat', 'Tiguan', 'Jetta'],
            'Ford' => ['Focus', 'Fusion', 'Escape', 'Fiesta'],
            'Hyundai' => ['Elantra', 'Sonata', 'Tucson', 'Santa Fe'],
            'Kia' => ['Rio', 'Ceed', 'Sportage', 'Sorento'],
            'Honda' => ['Civic', 'Accord', 'CR-V', 'Fit'],
            'Nissan' => ['Sentra', 'Altima', 'X-Trail', 'Qashqai'],
            default => ['A3', 'A4', 'Q5', 'Passat'],
        });

        $part = $this->faker->randomElement([
            'Alternator',
            'Starter Motor',
            'Brake Pads (Front)',
            'Brake Discs (Rear)',
            'Radiator',
            'A/C Compressor',
            'Oil Filter',
            'Air Filter',
            'Fuel Pump',
            'Ignition Coil',
            'Oxygen Sensor',
            'Wheel Hub Bearing',
            'Shock Absorber (Front)',
            'CV Axle',
            'Headlight (Left)',
            'Tail Light (Right)',
            'Door Mirror',
            'Turbocharger',
            'Water Pump',
            'Timing Chain Kit',
        ]);

        $isUnique = $this->faker->boolean(35);
        $sku = $this->faker->boolean(70) ? strtoupper($this->faker->bothify('CP-####??')) : null;

        return [
            'sku' => $sku,
            'name' => "{$part} — {$make} {$model}",
            'description' => $this->faker->boolean(60) ? $this->faker->paragraph() : null,
            'category_id' => null, // assigned by seeder for realism/consistency
            'condition_type_id' => $this->faker->optional(0.7)->randomElement([1, 2]), // 1=new, 2=used (placeholder)
            'quantity_in_stock' => 0, // set from inventory movements by seeder
            'cost_price' => $this->faker->optional(0.8)->randomFloat(2, 10, 800),
            'sale_price' => $this->faker->optional(0.95)->randomFloat(2, 15, 1200),
            'currency_id' => $this->faker->optional(0.7)->randomElement([1, 2]), // 1=GEL, 2=USD (placeholder)
            'phone' => $this->faker->optional(0.25)->numerify('+9955#######'),
            'location_label' => $this->faker->optional(0.6)->randomElement(['Tbilisi', 'Kutaisi', 'Batumi', 'Rustavi']),
            'is_active' => $this->faker->boolean(92),
            'notes' => $this->faker->optional(0.25)->sentence(),
        ];
    }
}
