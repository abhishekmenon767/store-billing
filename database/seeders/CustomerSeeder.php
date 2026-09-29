<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::updateOrCreate(['email' => 'thomas@example.com'], ['name' => 'Thomas Mathew']);
        Customer::updateOrCreate(['email' => 'priya@example.com'], ['name' => 'Priya Nair']);

        Customer::factory()->count(8)->create();
    }
}
