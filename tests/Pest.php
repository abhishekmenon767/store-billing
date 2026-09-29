<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

pest()->extend(TestCase::class)
    ->in('Concurrency');

function orderPayload(array $items, string $email = 'thomas@example.com', ?string $name = 'Thomas', mixed $amountPaid = null): array
{
    return array_filter([
        'customer' => ['email' => $email, 'name' => $name],
        'items' => $items,
        'amount_paid' => $amountPaid,
    ], fn ($value) => $value !== null);
}
