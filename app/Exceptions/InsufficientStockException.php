<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(public readonly array $shortages)
    {
        parent::__construct('Insufficient stock for: '.implode(', ', array_column($shortages, 'name')).'.');
    }
}
