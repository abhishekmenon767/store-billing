<?php

use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();

    $this->toothpaste = Product::factory()->create(['name' => 'Toothpaste', 'price' => 50, 'tax_percent' => 18, 'stock' => 10]);
    $this->biscuit = Product::factory()->create(['name' => 'Biscuit', 'price' => 10, 'tax_percent' => 18, 'stock' => 20]);
});

it('creates an order, calculates totals and deducts stock', function () {
    $this->postJson('/api/orders', orderPayload([
        ['product_id' => $this->toothpaste->id, 'quantity' => 2],
        ['product_id' => $this->biscuit->id, 'quantity' => 5],
    ]))
        ->assertCreated()
        ->assertJsonPath('data.subtotal', '150.00')
        ->assertJsonPath('data.tax_total', '27.00')
        ->assertJsonPath('data.grand_total', '177.00')
        ->assertJsonCount(2, 'data.items');

    expect($this->toothpaste->fresh()->stock)->toBe(8)
        ->and($this->biscuit->fresh()->stock)->toBe(15);

    Queue::assertPushed(SendOrderConfirmation::class);
});

it('uses the existing customer when the email is already registered', function () {
    Customer::factory()->create(['email' => 'thomas@example.com', 'name' => 'Thomas Mathew']);

    $this->postJson('/api/orders', orderPayload([['product_id' => $this->biscuit->id, 'quantity' => 1]]))
        ->assertCreated()
        ->assertJsonPath('data.customer.name', 'Thomas Mathew');

    expect(Customer::count())->toBe(1);
});

it('rejects the order when stock is insufficient', function () {
    $this->postJson('/api/orders', orderPayload([['product_id' => $this->toothpaste->id, 'quantity' => 11]]))
        ->assertStatus(409)
        ->assertJsonPath('errors.shortages.0.available', 10);

    expect($this->toothpaste->fresh()->stock)->toBe(10)
        ->and(Order::count())->toBe(0);

    Queue::assertNothingPushed();
});

it('does not save any line when one line is short', function () {
    $this->postJson('/api/orders', orderPayload([
        ['product_id' => $this->biscuit->id, 'quantity' => 5],
        ['product_id' => $this->toothpaste->id, 'quantity' => 50],
    ]))->assertStatus(409);

    expect($this->biscuit->fresh()->stock)->toBe(20)
        ->and(Order::count())->toBe(0);
});

it('sells the last unit only once', function () {
    $product = Product::factory()->create(['stock' => 1]);
    $payload = orderPayload([['product_id' => $product->id, 'quantity' => 1]]);

    $this->postJson('/api/orders', $payload)->assertCreated();
    $this->postJson('/api/orders', $payload)->assertStatus(409);

    expect($product->fresh()->stock)->toBe(0);
});

it('calculates the change to return', function () {
    $this->postJson('/api/orders', orderPayload([['product_id' => $this->toothpaste->id, 'quantity' => 2]], amountPaid: 200))
        ->assertCreated()
        ->assertJsonPath('data.change_due', '82.00');
});

it('rejects an amount paid lower than the grand total', function () {
    $this->postJson('/api/orders', orderPayload([['product_id' => $this->toothpaste->id, 'quantity' => 2]], amountPaid: 100))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('amount_paid');

    expect($this->toothpaste->fresh()->stock)->toBe(10);
});

it('validates the request', function () {
    $this->postJson('/api/orders', [
        'customer' => ['email' => 'not-an-email'],
        'items' => [['product_id' => 999, 'quantity' => 0]],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer.email', 'customer.name', 'items.0.product_id', 'items.0.quantity']);
});
