<?php

use App\Actions\PlaceOrderAction;
use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    Queue::fake();
});

afterEach(function () {
    DB::reconnect();
    Artisan::call('migrate:fresh');
});

function placeOrdersAtOnce(int $count, Closure $items): array
{
    DB::disconnect();
    $startAt = microtime(true) + 0.5;
    $pids = [];

    for ($i = 0; $i < $count; $i++) {
        $pid = pcntl_fork();

        if ($pid === 0) {
            DB::purge();
            time_sleep_until($startAt);

            try {
                app(PlaceOrderAction::class)->execute([
                    'customer' => ['email' => 'buyer@example.com', 'name' => 'Buyer'],
                    'items' => $items($i),
                ]);
                exit(0);
            } catch (InsufficientStockException) {
                exit(1);
            }
        }

        $pids[] = $pid;
    }

    return collect($pids)->map(function ($pid) {
        pcntl_waitpid($pid, $status);

        return match (pcntl_wexitstatus($status)) {
            0 => 'success',
            1 => 'out_of_stock',
            default => 'error',
        };
    })->countBy()->all();
}

it('sells the last unit to only one of many buyers', function () {
    $product = Product::factory()->create(['stock' => 1]);

    $results = placeOrdersAtOnce(8, fn () => [['product_id' => $product->id, 'quantity' => 1]]);

    expect($results)->toEqual(['success' => 1, 'out_of_stock' => 7])
        ->and($product->fresh()->stock)->toBe(0)
        ->and(Order::count())->toBe(1);
});

it('does not deadlock when orders list products in a different order', function () {
    $a = Product::factory()->create(['stock' => 10]);
    $b = Product::factory()->create(['stock' => 10]);

    $results = placeOrdersAtOnce(10, fn ($i) => $i % 2
        ? [['product_id' => $a->id, 'quantity' => 1], ['product_id' => $b->id, 'quantity' => 1]]
        : [['product_id' => $b->id, 'quantity' => 1], ['product_id' => $a->id, 'quantity' => 1]]);

    expect($results)->toEqual(['success' => 10])
        ->and($a->fresh()->stock)->toBe(0)
        ->and($b->fresh()->stock)->toBe(0);
});
