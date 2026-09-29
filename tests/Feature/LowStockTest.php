<?php

use App\Models\Product;

beforeEach(function () {
    Product::factory()->create(['name' => 'Eggs', 'stock' => 2]);
    Product::factory()->create(['name' => 'Milk', 'stock' => 9]);
    Product::factory()->create(['name' => 'Soap', 'stock' => 50]);
});

it('lists products below the configured threshold', function () {
    config(['store.low_stock_threshold' => 10]);

    $this->getJson('/api/products/low-stock')
        ->assertOk()
        ->assertJsonPath('data.*.name', ['Eggs', 'Milk']);
});

it('accepts a threshold in the query string', function () {
    $this->getJson('/api/products/low-stock?threshold=5')
        ->assertOk()
        ->assertJsonPath('data.*.name', ['Eggs']);
});
