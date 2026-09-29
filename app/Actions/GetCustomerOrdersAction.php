<?php

namespace App\Actions;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetCustomerOrdersAction
{
    public function execute(Customer $customer, int $perPage = 15): LengthAwarePaginator
    {
        return $customer->orders()
            ->with('items')
            ->latest()
            ->latest('id')
            ->paginate($perPage);
    }
}
