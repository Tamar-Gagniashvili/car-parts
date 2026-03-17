<?php

namespace Database\Seeders;

use App\Enums\InventoryMovementType;
use App\Enums\MarketplaceChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SaleChannel;
use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\MarketplaceListing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVehicleFit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CrmDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $categories = $this->seedCategories();
            $products = $this->seedProducts($categories);

            $this->seedProductMediaAndFits($products);
            $this->seedInitialInventory($products);

            $customers = Customer::factory()->count(40)->create();
            $this->seedOrdersAndSales($customers, $products);

            $this->seedMarketplaceListings($products);

            $this->recalculateProductStockFromMovements();
        });
    }

    /**
     * @return Collection<int, Category>
     */
    private function seedCategories()
    {
        $names = [
            'Engine',
            'Transmission',
            'Suspension',
            'Brakes',
            'Electrical',
            'Cooling',
            'Exhaust',
            'Body & Exterior',
            'Interior',
            'Lighting',
            'Filters',
            'Fuel System',
            'Steering',
            'Wheels & Tires',
        ];

        $categories = collect();
        foreach ($names as $name) {
            $categories->push(Category::query()->firstOrCreate(['name' => $name]));
        }

        return $categories;
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return Collection<int, Product>
     */
    private function seedProducts($categories)
    {
        $categoryByKeyword = [
            'Brake' => 'Brakes',
            'Shock' => 'Suspension',
            'Wheel' => 'Wheels & Tires',
            'Radiator' => 'Cooling',
            'Water Pump' => 'Cooling',
            'Timing' => 'Engine',
            'Turbo' => 'Engine',
            'Oil Filter' => 'Filters',
            'Air Filter' => 'Filters',
            'Fuel Pump' => 'Fuel System',
            'Ignition' => 'Electrical',
            'Oxygen Sensor' => 'Electrical',
            'Headlight' => 'Lighting',
            'Tail Light' => 'Lighting',
            'Door Mirror' => 'Body & Exterior',
            'CV Axle' => 'Transmission',
            'Starter' => 'Electrical',
            'Alternator' => 'Electrical',
            'A/C' => 'Cooling',
        ];

        $products = Product::factory()->count(120)->create()->each(function (Product $product) use ($categories, $categoryByKeyword) {
            $name = $product->name;

            $targetCategoryName = null;
            foreach ($categoryByKeyword as $keyword => $catName) {
                if (str_contains($name, $keyword)) {
                    $targetCategoryName = $catName;
                    break;
                }
            }

            if ($targetCategoryName) {
                $cat = $categories->firstWhere('name', $targetCategoryName);
            } else {
                $cat = $categories->random();
            }

            $product->category()->associate($cat);

            // Align pricing a bit: sale >= cost when both present
            $cost = $product->cost_price ?? null;
            $sale = $product->sale_price ?? null;
            if ($cost !== null && $sale !== null && (float) $sale < (float) $cost) {
                $product->sale_price = (float) $cost + (float) mt_rand(5, 40);
            }

            $product->save();
        });

        return $products;
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function seedProductMediaAndFits($products): void
    {
        foreach ($products as $product) {
            $imageCount = random_int(1, 4);
            for ($i = 0; $i < $imageCount; $i++) {
                ProductImage::factory()->create([
                    'product_id' => $product->id,
                    'sort_order' => $i,
                ]);
            }

            $fitCount = random_int(1, 3);
            $mainIdx = random_int(0, $fitCount - 1);
            for ($i = 0; $i < $fitCount; $i++) {
                ProductVehicleFit::factory()->create([
                    'product_id' => $product->id,
                    'is_main' => $i === $mainIdx,
                ]);
            }
        }
    }

    /**
     * Seed realistic starting stock with "in" movements.
     *
     * @param  Collection<int, Product>  $products
     */
    private function seedInitialInventory($products): void
    {
        foreach ($products as $product) {
            // Some products are one-offs, most are small quantity repeats.
            $startingQty = random_int(1, 6);
            if (random_int(1, 100) <= 30) {
                $startingQty = 1;
            }

            InventoryMovement::query()->create([
                'product_id' => $product->id,
                'type' => InventoryMovementType::In,
                'quantity' => $startingQty,
                'note' => 'Initial stock',
                'reference_type' => null,
                'reference_id' => null,
                'created_by' => null,
            ]);
        }
    }

    /**
     * Create orders with items and corresponding "sale" movements.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Customer>  $customers
     * @param  Collection<int, Product>  $products
     */
    private function seedOrdersAndSales($customers, $products): void
    {
        $orderCount = 60;

        for ($i = 0; $i < $orderCount; $i++) {
            /** @var Customer|null $customer */
            $customer = random_int(1, 100) <= 85 ? $customers->random() : null;

            /** @var Order $order */
            $order = Order::factory()->create([
                'customer_id' => $customer?->id,
                'status' => Arr::random([OrderStatus::Confirmed->value, OrderStatus::Completed->value]),
                'payment_status' => Arr::random([PaymentStatus::Paid->value, PaymentStatus::Unpaid->value, PaymentStatus::Partial->value]),
                'sale_channel' => Arr::random([SaleChannel::Internal->value, SaleChannel::MyParts->value]),
                'sold_at' => now()->subDays(random_int(0, 45)),
            ]);

            $itemsCount = random_int(1, 4);
            $subtotal = 0.0;

            $pickedProducts = $products->random($itemsCount);
            foreach ($pickedProducts as $product) {
                $qty = random_int(1, 2);
                $unitPrice = (float) ($product->sale_price ?? random_int(30, 600));

                $itemTotal = round($unitPrice * $qty, 2);
                $subtotal += $itemTotal;

                $item = OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'total_price' => $itemTotal,
                ]);

                // Inventory movement for the sale.
                InventoryMovement::query()->create([
                    'product_id' => $product->id,
                    'type' => InventoryMovementType::Sale,
                    'quantity' => $qty,
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'note' => 'Sold via order '.$order->order_number,
                    'created_by' => null,
                ]);
            }

            $discount = random_int(1, 100) <= 25 ? round($subtotal * (random_int(5, 15) / 100), 2) : 0.0;
            $total = max(0, round($subtotal - $discount, 2));

            $order->update([
                'subtotal' => round($subtotal, 2),
                'discount' => $discount,
                'total' => $total,
            ]);
        }
    }

    /**
     * Create marketplace listings for a subset of products and store a raw payload.
     *
     * @param  Collection<int, Product>  $products
     */
    private function seedMarketplaceListings($products): void
    {
        $listedProducts = $products->random(70);

        foreach ($listedProducts as $product) {
            $listing = MarketplaceListing::factory()->create([
                'product_id' => $product->id,
                'channel' => MarketplaceChannel::MyParts->value,
                'external_price' => $product->sale_price,
                'external_quantity' => random_int(1, 5),
            ]);

            $listing->update([
                'raw_payload' => [
                    'product_id' => $listing->external_id,
                    'title' => $product->name,
                    'category_title' => $listing->external_category_title,
                    'cat_id' => $listing->external_category_id,
                    'price' => $listing->external_price,
                    'currency_id' => $listing->external_currency_id,
                    'quantity' => $listing->external_quantity,
                    'status_id' => $listing->external_status_id,
                    'views' => $listing->views,
                    'create_date' => optional($listing->create_date)->toIso8601String(),
                    'update_date' => optional($listing->update_date)->toIso8601String(),
                    'end_date' => optional($listing->end_date)->toIso8601String(),
                    'photos' => $product->images()->limit(3)->get(['thumb_url', 'large_url'])->toArray(),
                    'models' => $product->vehicleFits()->limit(3)->get(['manufacturer_external_id', 'model_external_id', 'year_from', 'year_to', 'volume'])->toArray(),
                    'langs' => [
                        'ka' => $product->name,
                        'en' => $product->name,
                    ],
                ],
                'last_synced_at' => now()->subHours(random_int(1, 48)),
            ]);
        }
    }

    private function recalculateProductStockFromMovements(): void
    {
        // Compute stock from movements: in/return/adjustment add, out/sale/reserve subtract.
        $add = [InventoryMovementType::In->value, InventoryMovementType::Return->value, InventoryMovementType::Adjustment->value];
        $sub = [InventoryMovementType::Out->value, InventoryMovementType::Sale->value, InventoryMovementType::Reserve->value];

        $productIds = Product::query()->pluck('id');
        foreach ($productIds as $productId) {
            $inQty = (int) InventoryMovement::query()
                ->where('product_id', $productId)
                ->whereIn('type', $add)
                ->sum('quantity');

            $outQty = (int) InventoryMovement::query()
                ->where('product_id', $productId)
                ->whereIn('type', $sub)
                ->sum('quantity');

            $onHand = max(0, $inQty - $outQty);

            Product::query()->whereKey($productId)->update([
                'quantity_in_stock' => $onHand,
            ]);
        }
    }
}
