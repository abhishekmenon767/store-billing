<?php

namespace Database\Seeders;

use App\Actions\PlaceOrderAction;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(PlaceOrderAction $placeOrder): void
    {
        $id = fn (string $code) => Product::where('code', $code)->value('id');

        $placeOrder->execute([
            'customer' => ['email' => 'thomas@example.com', 'name' => 'Thomas Mathew'],
            'items' => [
                ['product_id' => $id('COL-100'), 'quantity' => 2],
                ['product_id' => $id('PARLEG-1'), 'quantity' => 5],
            ],
            'amount_paid' => '200.00',
        ]);

        $placeOrder->execute([
            'customer' => ['email' => 'thomas@example.com', 'name' => 'Thomas Mathew'],
            'items' => [
                ['product_id' => $id('ATTA-5KG'), 'quantity' => 1],
                ['product_id' => $id('TATA-SALT'), 'quantity' => 1],
            ],
        ]);

        $placeOrder->execute([
            'customer' => ['email' => 'priya@example.com', 'name' => 'Priya Nair'],
            'items' => [
                ['product_id' => $id('MAGGI-70'), 'quantity' => 6],
                ['product_id' => $id('AMUL-BTR'), 'quantity' => 1],
            ],
        ]);
    }
}
