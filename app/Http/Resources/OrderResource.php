<?php

namespace App\Http\Resources;

use App\Actions\CalculateChangeAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'grand_total' => $this->grand_total,
            'amount_paid' => $this->amount_paid,
            'change_due' => $this->change_due,
            'change_breakdown' => $this->when(
                $this->change_due !== null,
                fn () => app(CalculateChangeAction::class)->execute((float) $this->change_due),
            ),
            'confirmation_sent_at' => $this->confirmation_sent_at,
            'created_at' => $this->created_at,
            'links' => [
                'bill_pdf' => route('orders.bill', $this->resource),
            ],
        ];
    }
}
