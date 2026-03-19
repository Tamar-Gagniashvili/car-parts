<?php

namespace App\Data\MyParts;

use Illuminate\Support\Arr;

class MyPartsProductData
{
    public function __construct(
        public int $productId,
        public string $title,
        public ?string $categoryTitle,
        public ?int $categoryId,
        public ?int $condTypeId,
        public ?float $price,
        public ?int $currencyId,
        public ?string $phone,
        public ?int $quantity,
        public ?int $views,
        public ?int $statusId,
        public ?string $createDate,
        public ?string $updateDate,
        public ?string $endDate,
        public array $photos,
        public array $models,
        public array $langs,
        public array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            title: (string) $data['title'],
            categoryTitle: Arr::get($data, 'category_title'),
            categoryId: Arr::get($data, 'cat_id'),
            condTypeId: Arr::get($data, 'cond_type_id'),
            price: isset($data['price']) ? (float) $data['price'] : null,
            currencyId: Arr::get($data, 'currency_id'),
            phone: Arr::get($data, 'phone'),
            quantity: isset($data['quantity']) ? (int) $data['quantity'] : null,
            views: isset($data['views']) ? (int) $data['views'] : null,
            statusId: Arr::get($data, 'status_id'),
            createDate: Arr::get($data, 'create_date'),
            updateDate: Arr::get($data, 'update_date'),
            endDate: Arr::get($data, 'end_date'),
            photos: Arr::get($data, 'photos', []),
            models: Arr::get($data, 'models', []),
            langs: Arr::get($data, 'langs', []),
            raw: $data,
        );
    }
}
