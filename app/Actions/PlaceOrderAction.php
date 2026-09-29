<?php

namespace App\Actions;

use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlaceOrderAction
{
    public function execute(array $data): Order
    {
        $quantities = collect($data['items'])
            ->groupBy('product_id')
            ->map(fn (Collection $lines) => $lines->sum('quantity'))
            ->sortKeys();

        $order = DB::transaction(function () use ($data, $quantities) {
            // Rows are locked in id order so two orders can never deadlock each other.
            $products = Product::whereKey($quantities->keys())->orderBy('id')->lockForUpdate()->get();

            $this->checkStock($products, $quantities);

            $customer = Customer::firstOrCreate(
                ['email' => Str::lower($data['customer']['email'])],
                ['name' => $data['customer']['name']],
            );

            $items = $products->map(fn (Product $product) => $this->buildItem($product, $quantities[$product->id]));

            $subtotal = round($items->sum('line_subtotal'), 2);
            $taxTotal = round($items->sum('line_tax'), 2);
            $grandTotal = round($subtotal + $taxTotal, 2);
            $amountPaid = $data['amount_paid'] ?? null;

            if ($amountPaid !== null && $amountPaid < $grandTotal) {
                throw ValidationException::withMessages([
                    'amount_paid' => "Amount paid is less than the grand total ({$grandTotal}).",
                ]);
            }

            $order = $customer->orders()->create([
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'amount_paid' => $amountPaid,
                'change_due' => $amountPaid !== null ? round($amountPaid - $grandTotal, 2) : null,
            ]);

            $order->items()->createMany($items);

            $products->each(fn (Product $product) => $this->deductStock($product, $quantities[$product->id], $order));

            return $order;
        }, 3);

        SendOrderConfirmation::dispatch($order);

        return $order->load(['customer', 'items']);
    }

    private function checkStock($products, Collection $quantities): void
    {
        $shortages = $products
            ->filter(fn (Product $product) => $product->stock < $quantities[$product->id])
            ->map(fn (Product $product) => [
                'product_id' => $product->id,
                'name' => $product->name,
                'requested' => $quantities[$product->id],
                'available' => $product->stock,
            ])
            ->values()
            ->all();

        if ($shortages) {
            throw new InsufficientStockException($shortages);
        }
    }

    private function buildItem(Product $product, int $quantity): array
    {
        $lineSubtotal = round($product->price * $quantity, 2);
        $lineTax = round($lineSubtotal * $product->tax_percent / 100, 2);

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'unit_price' => $product->price,
            'tax_percent' => $product->tax_percent,
            'quantity' => $quantity,
            'line_subtotal' => $lineSubtotal,
            'line_tax' => $lineTax,
            'line_total' => round($lineSubtotal + $lineTax, 2),
        ];
    }

    private function deductStock(Product $product, int $quantity, Order $order): void
    {
        $updated = Product::whereKey($product->id)
            ->where('stock', '>=', $quantity)
            ->decrement('stock', $quantity);

        if (! $updated) {
            throw new InsufficientStockException([[
                'product_id' => $product->id,
                'name' => $product->name,
                'requested' => $quantity,
                'available' => $product->fresh()->stock,
            ]]);
        }

        $product->stockMovements()->create([
            'order_id' => $order->id,
            'reason' => StockMovement::REASON_SALE,
            'quantity_change' => -$quantity,
            'balance_after' => $product->stock - $quantity,
        ]);
    }
}
