<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'product_code' => $this->product_code,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'tax_percent' => $this->tax_percent,
            'line_subtotal' => $this->line_subtotal,
            'line_tax' => $this->line_tax,
            'line_total' => $this->line_total,
        ];
    }
}
