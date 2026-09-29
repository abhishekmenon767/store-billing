<?php

namespace App\Actions;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateOrderBillAction
{
    public function execute(Order $order): string
    {
        return Pdf::loadView('pdf.bill', ['order' => $order->loadMissing(['customer', 'items'])])->output();
    }
}
