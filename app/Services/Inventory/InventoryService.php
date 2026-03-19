<?php

namespace App\Services\Inventory;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventoryService
{
    public function stockIn(
        Product $product,
        int $quantity,
        ?string $note = null,
        ?User $createdBy = null,
        ?Model $reference = null,
    ): InventoryMovement {
        return $this->applyMovement(
            product: $product,
            type: InventoryMovementType::In,
            quantity: $quantity,
            note: $note,
            createdBy: $createdBy,
            reference: $reference,
            allowNegative: false,
        );
    }

    public function stockOut(
        Product $product,
        int $quantity,
        ?string $note = null,
        ?User $createdBy = null,
        ?Model $reference = null,
        bool $allowNegative = false,
    ): InventoryMovement {
        return $this->applyMovement(
            product: $product,
            type: InventoryMovementType::Out,
            quantity: $quantity,
            note: $note,
            createdBy: $createdBy,
            reference: $reference,
            allowNegative: $allowNegative,
        );
    }

    public function reserve(
        Product $product,
        int $quantity,
        ?string $note = null,
        ?User $createdBy = null,
        ?Model $reference = null,
        bool $allowNegative = false,
    ): InventoryMovement {
        return $this->applyMovement(
            product: $product,
            type: InventoryMovementType::Reserve,
            quantity: $quantity,
            note: $note,
            createdBy: $createdBy,
            reference: $reference,
            allowNegative: $allowNegative,
        );
    }

    public function returnToStock(
        Product $product,
        int $quantity,
        ?string $note = null,
        ?User $createdBy = null,
        ?Model $reference = null,
    ): InventoryMovement {
        return $this->applyMovement(
            product: $product,
            type: InventoryMovementType::Return,
            quantity: $quantity,
            note: $note,
            createdBy: $createdBy,
            reference: $reference,
            allowNegative: false,
        );
    }

    public function deductOnSale(
        Product $product,
        int $quantity,
        ?string $note = null,
        ?User $createdBy = null,
        ?Model $reference = null,
        bool $allowNegative = false,
    ): InventoryMovement {
        return $this->applyMovement(
            product: $product,
            type: InventoryMovementType::Sale,
            quantity: $quantity,
            note: $note,
            createdBy: $createdBy,
            reference: $reference,
            allowNegative: $allowNegative,
        );
    }

    /**
     * Adjust stock to an absolute target quantity.
     *
     * Creates a single adjustment movement with a signed delta quantity.
     */
    public function adjustTo(
        Product $product,
        int $targetQuantity,
        ?string $note = null,
        ?User $createdBy = null,
        ?Model $reference = null,
        bool $allowNegative = false,
    ): InventoryMovement {
        if ($targetQuantity < 0 && (! $allowNegative)) {
            throw new InvalidArgumentException('Target quantity cannot be negative.');
        }

        return DB::transaction(function () use ($product, $targetQuantity, $note, $createdBy, $reference, $allowNegative) {
            /** @var Product $locked */
            $locked = Product::query()
                ->whereKey($product->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $current = (int) $locked->quantity_in_stock;
            $delta = $targetQuantity - $current;

            if ($delta === 0) {
                throw new InvalidArgumentException('Adjustment would not change stock.');
            }

            $newQuantity = $current + $delta;
            if ($newQuantity < 0 && (! $allowNegative)) {
                throw new RuntimeException('Insufficient stock for this adjustment.');
            }

            $movement = new InventoryMovement([
                'product_id' => $locked->id,
                'type' => InventoryMovementType::Adjustment,
                'quantity' => $delta, // signed
                'note' => $note,
                'created_by' => $createdBy?->id,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
            ]);
            $movement->save();

            $locked->quantity_in_stock = $newQuantity;
            $locked->save();

            return $movement;
        });
    }

    private function applyMovement(
        Product $product,
        InventoryMovementType $type,
        int $quantity,
        ?string $note,
        ?User $createdBy,
        ?Model $reference,
        bool $allowNegative,
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than 0.');
        }

        $delta = $this->deltaFor($type, $quantity);

        return DB::transaction(function () use ($product, $type, $quantity, $delta, $note, $createdBy, $reference, $allowNegative) {
            /** @var Product $locked */
            $locked = Product::query()
                ->whereKey($product->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $current = (int) $locked->quantity_in_stock;
            $newQuantity = $current + $delta;

            if ($newQuantity < 0 && (! $allowNegative)) {
                throw new RuntimeException('Insufficient stock for this operation.');
            }

            $movement = new InventoryMovement([
                'product_id' => $locked->id,
                'type' => $type,
                'quantity' => $quantity,
                'note' => $note,
                'created_by' => $createdBy?->id,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
            ]);
            $movement->save();

            $locked->quantity_in_stock = $newQuantity;
            $locked->save();

            return $movement;
        });
    }

    private function deltaFor(InventoryMovementType $type, int $quantity): int
    {
        return match ($type) {
            InventoryMovementType::In,
            InventoryMovementType::Return => +$quantity,

            InventoryMovementType::Out,
            InventoryMovementType::Sale,
            InventoryMovementType::Reserve => -$quantity,

            InventoryMovementType::Adjustment => throw new InvalidArgumentException('Use adjustTo() for adjustments.'),
        };
    }
}
