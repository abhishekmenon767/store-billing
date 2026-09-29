<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucwords(fake()->words(2, true)),
            'code' => fake()->unique()->bothify('SKU-####??'),
            'price' => fake()->randomFloat(2, 5, 500),
            'tax_percent' => fake()->randomElement([0, 5, 12, 18]),
            'stock' => fake()->numberBetween(20, 200),
        ];
    }

    public function stock(int $stock): static
    {
        return $this->state(['stock' => $stock]);
    }

    public function lowStock(): static
    {
        return $this->state(fn () => ['stock' => fake()->numberBetween(1, 5)]);
    }

    public function outOfStock(): static
    {
        return $this->stock(0);
    }
}
