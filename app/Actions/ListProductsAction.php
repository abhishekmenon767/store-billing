<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ListProductsAction
{
    public function execute(): Collection
    {
        return Product::orderBy('name')->get();
    }
}
