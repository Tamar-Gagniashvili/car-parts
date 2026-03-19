<?php

namespace App\Services\MyParts;

use App\Data\MyParts\MyPartsProductData;
use App\Enums\MarketplaceChannel;
use App\Models\Category;
use App\Models\MarketplaceCategoryMapping;
use App\Models\MarketplaceListing;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVehicleFit;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MyPartsImportService
{
    /**
     * Sync a single paginated response payload from MyParts.
     *
     * Expected shape: ['data' => ['products' => [...]]]
     */
    public function syncProductsPayload(array $payload): void
    {
        $products = Arr::get($payload, 'data.products', []);

        foreach ($products as $productArray) {
            $this->syncSingleProduct(MyPartsProductData::fromArray($productArray));
        }
    }

    protected function syncSingleProduct(MyPartsProductData $data): void
    {
        DB::transaction(function () use ($data) {
            $listing = MarketplaceListing::query()
                ->where('channel', MarketplaceChannel::MyParts->value)
                ->where('external_id', (string) $data->productId)
                ->first();

            $product = null;

            if ($listing && $listing->product) {
                $product = $listing->product;
            }

            // Always track the external category mapping (even on re-sync),
            // but only apply it to the product if the product doesn't already have a category.
            $mappedCategory = $this->resolveCategoryFromMyParts($data);

            if (! $product) {
                $product = $this->findOrCreateProductFromListing($data);
            } else {
                $this->updateProductFromListing($product, $data);
            }

            if (! $product->category_id && $mappedCategory) {
                $product->category()->associate($mappedCategory)->save();
            }

            $listing = $this->upsertListing($listing, $product, $data);

            $this->syncImages($product, $data);
            $this->syncVehicleFits($product, $data);
        });
    }

    protected function findOrCreateProductFromListing(MyPartsProductData $data): Product
    {
        $category = $this->resolveCategoryFromMyParts($data);

        // Deduplication heuristic: same name + category + phone is considered the same product.
        $query = Product::query()->where('name', $data->title);

        if ($category) {
            $query->where('category_id', $category->id);
        }

        if ($data->phone) {
            $query->where('phone', $data->phone);
        }

        $existing = $query->first();

        if ($existing) {
            $this->updateProductFromListing($existing, $data);

            return $existing;
        }

        return Product::query()->create([
            'sku' => null,
            'name' => $data->title,
            'description' => null,
            'category_id' => $category?->id,
            'condition_type_id' => $data->condTypeId,
            'quantity_in_stock' => 0,
            'cost_price' => null,
            'sale_price' => $data->price,
            'currency_id' => $data->currencyId,
            'phone' => $data->phone,
            'location_label' => null,
            'is_active' => true,
            'notes' => 'Imported from MyParts.ge',
        ]);
    }

    protected function updateProductFromListing(Product $product, MyPartsProductData $data): void
    {
        $dirty = [];

        if (! $product->category_id) {
            $category = $this->resolveCategoryFromMyParts($data, createIfMissing: false);
            if ($category) {
                $dirty['category_id'] = $category->id;
            }
        }

        if ($data->condTypeId !== null) {
            $dirty['condition_type_id'] = $data->condTypeId;
        }

        if ($data->price !== null && $product->sale_price === null) {
            $dirty['sale_price'] = $data->price;
        }

        if ($data->currencyId !== null && $product->currency_id === null) {
            $dirty['currency_id'] = $data->currencyId;
        }

        if ($data->phone && $product->phone === null) {
            $dirty['phone'] = $data->phone;
        }

        if (! empty($dirty)) {
            $product->fill($dirty)->save();
        }
    }

    protected function resolveCategoryFromMyParts(MyPartsProductData $data, bool $createIfMissing = true): ?Category
    {
        if ($data->categoryId === null && ! $data->categoryTitle) {
            return null;
        }

        if ($data->categoryId !== null) {
            $mapping = MarketplaceCategoryMapping::query()->updateOrCreate(
                [
                    'channel' => MarketplaceChannel::MyParts->value,
                    'external_category_id' => (int) $data->categoryId,
                ],
                [
                    'external_category_title' => $data->categoryTitle,
                    'last_seen_at' => now(),
                    'raw_payload' => [
                        'cat_id' => $data->categoryId,
                        'category_title' => $data->categoryTitle,
                    ],
                ],
            );

            if ($mapping->category_id) {
                return Category::query()->find($mapping->category_id);
            }

            if (! $createIfMissing) {
                return null;
            }

            if ($data->categoryTitle) {
                $category = Category::query()->firstOrCreate(['name' => $data->categoryTitle]);
                $mapping->update(['category_id' => $category->id]);

                return $category;
            }

            return null;
        }

        // No external ID, fallback to title-only
        if (! $createIfMissing) {
            return Category::query()->where('name', $data->categoryTitle)->first();
        }

        return Category::query()->firstOrCreate(['name' => $data->categoryTitle]);
    }

    protected function upsertListing(?MarketplaceListing $listing, Product $product, MyPartsProductData $data): MarketplaceListing
    {
        if (! $listing) {
            $listing = new MarketplaceListing;
            $listing->channel = MarketplaceChannel::MyParts->value;
            $listing->external_id = (string) $data->productId;
        }

        $listing->product_id = $product->id;
        $listing->external_status_id = $data->statusId;
        $listing->external_category_id = $data->categoryId;
        $listing->external_category_title = $data->categoryTitle;
        $listing->external_price = $data->price;
        $listing->external_currency_id = $data->currencyId;
        $listing->external_quantity = $data->quantity;
        $firstPhoto = $data->photos[0] ?? [];
        $listing->external_thumb_url = Arr::get($firstPhoto, 'thumbs');
        $listing->external_large_url = Arr::get($firstPhoto, 'large');
        $listing->views = $data->views;
        $listing->create_date = $data->createDate;
        $listing->update_date = $data->updateDate;
        $listing->end_date = $data->endDate;
        $listing->raw_payload = $data->raw;
        $listing->last_synced_at = now();

        $listing->save();

        return $listing;
    }

    protected function syncImages(Product $product, MyPartsProductData $data): void
    {
        $sortOrder = 0;

        foreach ($data->photos as $photo) {
            $thumb = Arr::get($photo, 'thumbs');
            $large = Arr::get($photo, 'large');

            if (! $thumb && ! $large) {
                continue;
            }

            ProductImage::query()->firstOrCreate(
                [
                    'product_id' => $product->id,
                    'thumb_url' => $thumb,
                    'large_url' => $large,
                ],
                [
                    'sort_order' => $sortOrder++,
                ],
            );
        }
    }

    protected function syncVehicleFits(Product $product, MyPartsProductData $data): void
    {
        foreach ($data->models as $model) {
            $manufacturerId = Arr::get($model, 'man_id');
            $modelId = Arr::get($model, 'model_id');

            if ($manufacturerId === null || $modelId === null) {
                continue;
            }

            $yearFrom = Arr::get($model, 'year_from');
            $yearTo = Arr::get($model, 'year_to');

            ProductVehicleFit::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'manufacturer_external_id' => $manufacturerId,
                    'model_external_id' => $modelId,
                    'year_from' => $yearFrom,
                    'year_to' => $yearTo,
                ],
                [
                    'volume' => Arr::get($model, 'volume'),
                    'is_main' => (bool) Arr::get($model, 'main', false),
                ],
            );
        }
    }
}
