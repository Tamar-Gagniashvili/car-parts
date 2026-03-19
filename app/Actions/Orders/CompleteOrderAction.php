<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\User;
use App\Services\Orders\OrderService;

class CompleteOrderAction
{
    public function __construct(
        private readonly OrderService $orders,
    ) {}

    public function execute(Order $order, ?User $user = null): Order
    {
        return $this->orders->completeOrder($order, $user);
    }
}
