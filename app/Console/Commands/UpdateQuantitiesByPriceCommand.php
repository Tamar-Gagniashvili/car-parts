<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class UpdateQuantitiesByPriceCommand extends Command
{
    protected $signature = 'products:update-quantities-by-price {--dry-run : Show what would be updated without saving}';

    protected $description = 'Set random quantity_in_stock for each product based on its sale_price.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $products = Product::whereNotNull('sale_price')->get();

        if ($products->isEmpty()) {
            $this->info('No products with a sale price found.');

            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($products as $product) {
            $price = (float) $product->sale_price;
            [$min, $max] = $this->quantityRange($price);
            $quantity = rand($min, $max);

            if ($dryRun) {
                $this->line(sprintf(
                    '[dry-run] #%d "%s" price=%.2f → quantity %d (range %d–%d)',
                    $product->id,
                    $product->name,
                    $price,
                    $quantity,
                    $min,
                    $max,
                ));
            } else {
                $product->update(['quantity_in_stock' => $quantity]);
            }

            $updated++;
        }

        if ($dryRun) {
            $this->info("Dry run complete. {$updated} products would be updated.");
        } else {
            $this->info("Done. Updated quantity_in_stock for {$updated} products.");
        }

        return self::SUCCESS;
    }

    /** @return array{int, int} [min, max] */
    private function quantityRange(float $price): array
    {
        return match (true) {
            $price <= 500  => [15, 20],
            $price <= 1000 => [10, 15],
            $price <= 1500 => [8, 12],
            default        => [5, 10],
        };
    }
}
