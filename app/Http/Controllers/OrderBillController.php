<?php

namespace App\Http\Controllers;

use App\Actions\GenerateOrderBillAction;
use App\Models\Order;
use Illuminate\Http\Response;

class OrderBillController extends Controller
{
    public function show(Order $order, GenerateOrderBillAction $action): Response
    {
        return $this->pdfResponse($action->execute($order), "{$order->order_number}.pdf");
    }
}
