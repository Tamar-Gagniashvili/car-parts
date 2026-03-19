<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SaleChannel;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class OrderService
{
    public function __construct(
        private readonly InventoryService $inventory,
    ) {}

    /**
     * Create a new order (draft or completed) with items.
     *
     * $data keys:
     * - customer_id? int|null
     * - status? string (OrderStatus value)
     * - payment_status? string|null (PaymentStatus value)
     * - sale_channel? string (SaleChannel value)
     * - sold_at? \DateTimeInterface|string|null
     * - notes? string|null
     *
     * Items shape:
     * - product_id: int
     * - quantity: int
     * - unit_price?: float|null (falls back to product sale_price)
     */
    public function createOrder(array $data, array $items, ?User $user = null): Order
    {
        return DB::transaction(function () use ($data, $items, $user) {
            if (empty($items)) {
                throw new InvalidArgumentException('Order must contain at least one item.');
            }

            $status = isset($data['status'])
                ? OrderStatus::from($data['status'])
                : OrderStatus::Draft;

            $saleChannel = isset($data['sale_channel'])
                ? SaleChannel::from($data['sale_channel'])
                : SaleChannel::Internal;

            $paymentStatus = isset($data['payment_status'])
                ? PaymentStatus::from($data['payment_status'])
                : null;

            $customerId = Arr::get($data, 'customer_id');
            if ($customerId !== null && ! Customer::query()->whereKey($customerId)->exists()) {
                throw new InvalidArgumentException('Customer does not exist.');
            }

            /** @var Order $order */
            $order = new Order([
                'customer_id' => $customerId,
                'order_number' => Arr::get($data, 'order_number') ?? $this->generateOrderNumber(),
                'status' => $status,
                'payment_status' => $paymentStatus,
                'sale_channel' => $saleChannel,
                'subtotal' => 0,
                'discount' => (float) (Arr::get($data, 'discount') ?? 0),
                'total' => 0,
                'notes' => Arr::get($data, 'notes'),
                'sold_at' => Arr::get($data, 'sold_at'),
            ]);
            $order->save();

            $subtotal = 0.0;

            foreach ($items as $itemData) {
                $productId = Arr::get($itemData, 'product_id');
                $quantity = (int) Arr::get($itemData, 'quantity', 0);

                if (! $productId || $quantity <= 0) {
                    throw new InvalidArgumentException('Each item must have a product_id and quantity > 0.');
                }

                /** @var Product $product */
                $product = Product::query()->findOrFail($productId);

                $unitPrice = Arr::get($itemData, 'unit_price');
                if ($unitPrice === null) {
                    $unitPrice = $product->sale_price ?? $product->cost_price ?? 0.0;
                }

                $unitPrice = (float) $unitPrice;
                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'total_price' => $lineTotal,
                ]);
            }

            $discount = max(0.0, (float) $order->discount);
            $total = max(0.0, $subtotal - $discount);

            $order->update([
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
            ]);

            if ($status === OrderStatus::Completed) {
                $this->deductInventoryForOrder($order, $user);
            }

            return $order->fresh(['items', 'customer']);
        });
    }

    /**
     * Mark an existing order as completed and deduct stock (used when transitioning from draft to completed).
     */
    public function completeOrder(Order $order, ?User $user = null): Order
    {
        return DB::transaction(function () use ($order, $user) {
            $order->refresh();

            if ($order->status === OrderStatus::Completed) {
                throw new RuntimeException('Order is already completed.');
            }

            if ($order->items()->count() === 0) {
                throw new RuntimeException('Cannot complete an order without items.');
            }

            $this->deductInventoryForOrder($order, $user);

            $order->update([
                'status' => OrderStatus::Completed,
                'sold_at' => $order->sold_at ?? now(),
            ]);

            return $order->fresh(['items', 'customer']);
        });
    }

    /**
     * Simple detail update for draft orders (no stock operations).
     *
     * Does not allow editing items after completion to keep inventory consistent.
     */
    public function updateDraftOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $order->refresh();

            if ($order->status === OrderStatus::Completed) {
                throw new RuntimeException('Completed orders cannot be edited via this method.');
            }

            $customerId = Arr::get($data, 'customer_id', $order->customer_id);
            if ($customerId !== null && ! Customer::query()->whereKey($customerId)->exists()) {
                throw new InvalidArgumentException('Customer does not exist.');
            }

            $status = isset($data['status'])
                ? OrderStatus::from($data['status'])
                : $order->status;

            $saleChannel = isset($data['sale_channel'])
                ? SaleChannel::from($data['sale_channel'])
                : $order->sale_channel;

            $paymentStatus = array_key_exists('payment_status', $data) && $data['payment_status'] !== null
                ? PaymentStatus::from($data['payment_status'])
                : $order->payment_status;

            $order->update([
                'customer_id' => $customerId,
                'order_number' => Arr::get($data, 'order_number', $order->order_number),
                'status' => $status,
                'payment_status' => $paymentStatus,
                'sale_channel' => $saleChannel,
                'notes' => Arr::get($data, 'notes', $order->notes),
                'sold_at' => Arr::get($data, 'sold_at', $order->sold_at),
            ]);

            return $order->fresh(['items', 'customer']);
        });
    }

    protected function deductInventoryForOrder(Order $order, ?User $user = null): void
    {
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            /** @var Product|null $product */
            $product = $item->product;
            if (! $product) {
                continue;
            }

            $this->inventory->deductOnSale(
                product: $product,
                quantity: (int) $item->quantity,
                note: 'Order '.$order->order_number,
                createdBy: $user,
                reference: $order,
                allowNegative: false,
            );
        }
    }

    protected function generateOrderNumber(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd');
        $last = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('order_number');

        $next = 1;
        if ($last && preg_match('/(\d+)$/', $last, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%04d', $prefix, $next);
    }
}
