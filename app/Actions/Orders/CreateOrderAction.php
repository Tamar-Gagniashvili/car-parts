<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\User;
use App\Services\Orders\OrderService;

class CreateOrderAction
{
    public function __construct(
        private readonly OrderService $orders,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function execute(array $data, array $items, ?User $user = null): Order
    {
        return $this->orders->createOrder($data, $items, $user);
    }
}
