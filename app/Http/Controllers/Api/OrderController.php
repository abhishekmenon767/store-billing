<?php

namespace App\Http\Controllers\Api;

use App\Actions\GetOrderAction;
use App\Actions\PlaceOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, PlaceOrderAction $action): JsonResponse
    {
        $order = $action->execute($request->validated());

        return $this->createdResponse(new OrderResource($order), 'Order placed successfully');
    }

    public function show(Order $order, GetOrderAction $action): JsonResponse
    {
        return $this->successResponse(new OrderResource($action->execute($order)));
    }
}
