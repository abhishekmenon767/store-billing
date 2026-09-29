<?php

namespace App\Jobs;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderConfirmation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $order = $this->order->fresh(['customer', 'items']);

        if ($order === null || $order->confirmation_sent_at !== null) {
            return;
        }

        Mail::to($order->customer->email, $order->customer->name)
            ->send(new OrderConfirmationMail($order));

        $order->forceFill(['confirmation_sent_at' => now()])->save();

        Log::info('Order confirmation sent', [
            'order' => $order->order_number,
            'email' => $order->customer->email,
            'grand_total' => $order->grand_total,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Order confirmation failed', [
            'order' => $this->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
