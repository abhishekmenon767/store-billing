<?php

use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\LowStockProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/low-stock', [LowStockProductController::class, 'index'])->name('products.low-stock');

Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

Route::get('customers/{customer:email}/orders', [CustomerOrderController::class, 'index'])
    ->where('customer', '[^/]+')
    ->name('customers.orders');
