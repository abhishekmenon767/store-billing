<?php

use App\Http\Controllers\OrderBillController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'billing')->name('billing');
Route::get('orders/{order}/bill.pdf', [OrderBillController::class, 'show'])->name('orders.bill');
