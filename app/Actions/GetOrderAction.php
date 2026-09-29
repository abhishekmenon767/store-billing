<?php

namespace App\Actions;

use App\Models\Order;

class GetOrderAction
{
    public function execute(Order $order): Order
    {
        return $order->load(['customer', 'items']);
    }
}
