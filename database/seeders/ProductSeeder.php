<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private const CATALOG = [
        ['Colgate Toothpaste 100g', 'COL-100', '50.00', '18', 40],
        ['Parle-G Biscuit', 'PARLEG-1', '10.00', '18', 120],
        ['Tata Salt 1kg', 'TATA-SALT', '28.00', '0', 60],
        ['Aashirvaad Atta 5kg', 'ATTA-5KG', '285.00', '5', 25],
        ['Fortune Sunflower Oil 1L', 'FORT-OIL1', '155.00', '5', 30],
        ['Maggi Noodles 70g', 'MAGGI-70', '14.00', '12', 150],
        ['Surf Excel 1kg', 'SURF-1KG', '140.00', '18', 35],
        ['Red Label Tea 250g', 'RLTEA-250', '135.00', '5', 22],
        ['Amul Butter 100g', 'AMUL-BTR', '58.00', '12', 18],
        ['Dettol Soap 75g', 'DETTOL-75', '42.00', '18', 50],
        ['Bread', 'BREAD-400', '45.00', '0', 4],
        ['Milk 1L', 'MILK-1L', '66.00', '0', 9],
        ['Eggs (12)', 'EGGS-12', '84.00', '0', 2],
    ];

    public function run(): void
    {
        foreach (self::CATALOG as [$name, $code, $price, $tax, $stock]) {
            $product = Product::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'price' => $price, 'tax_percent' => $tax, 'stock' => $stock],
            );

            $product->stockMovements()->create([
                'reason' => StockMovement::REASON_RESTOCK,
                'quantity_change' => $stock,
                'balance_after' => $stock,
            ]);
        }
    }
}
