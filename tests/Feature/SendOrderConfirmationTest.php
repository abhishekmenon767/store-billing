<?php

use App\Jobs\SendOrderConfirmation;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

it('emails the customer only once', function () {
    Mail::fake();
    $order = Order::factory()->create();

    (new SendOrderConfirmation($order))->handle();
    (new SendOrderConfirmation($order))->handle();

    Mail::assertSent(OrderConfirmationMail::class, 1);
    expect($order->fresh()->confirmation_sent_at)->not->toBeNull();
});
