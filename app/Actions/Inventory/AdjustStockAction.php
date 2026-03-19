<?php

namespace App\Actions\Inventory;

use App\Models\Product;
use App\Models\User;
use App\Services\Inventory\InventoryService;
use Illuminate\Database\Eloquent\Model;

class AdjustStockAction
{
    public function __construct(
        private readonly InventoryService $inventory,
    ) {}

    public function execute(
        Product $product,
        int $targetQuantity,
        ?string $note = null,
        ?User $createdBy = null,
        ?Model $reference = null,
        bool $allowNegative = false,
    ) {
        return $this->inventory->adjustTo(
            product: $product,
            targetQuantity: $targetQuantity,
            note: $note,
            createdBy: $createdBy,
            reference: $reference,
            allowNegative: $allowNegative,
        );
    }
}
