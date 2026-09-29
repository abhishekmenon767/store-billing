<?php

namespace App\Http\Controllers\Api;

use App\Actions\GetCustomerOrdersAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CustomerOrderRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerOrderController extends Controller
{
    public function index(CustomerOrderRequest $request, Customer $customer, GetCustomerOrdersAction $action): JsonResponse
    {
        $orders = $action->execute($customer, $request->integer('per_page', 15));

        return $this->successResponse(
            OrderResource::collection($orders),
            meta: ['customer' => (new CustomerResource($customer->loadCount('orders')))->resolve()],
        );
    }
}
