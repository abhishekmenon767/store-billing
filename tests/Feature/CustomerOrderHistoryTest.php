<?php

use App\Models\Customer;
use App\Models\Order;

it('returns the customer order history, newest first', function () {
    $customer = Customer::factory()->create(['email' => 'thomas@example.com']);
    $old = Order::factory()->for($customer)->create(['created_at' => now()->subDay()]);
    $new = Order::factory()->for($customer)->create();
    Order::factory()->create();

    $this->getJson('/api/customers/thomas@example.com/orders')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.order_number', $new->order_number)
        ->assertJsonPath('data.1.order_number', $old->order_number);
});

it('returns 404 for an unknown email', function () {
    $this->getJson('/api/customers/nobody@example.com/orders')->assertNotFound();
});
