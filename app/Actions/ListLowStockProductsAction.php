<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ListLowStockProductsAction
{
    public function execute(int $threshold): Collection
    {
        return Product::belowStock($threshold)->get();
    }
}
