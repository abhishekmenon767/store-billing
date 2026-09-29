<?php

namespace App\Http\Controllers\Api;

use App\Actions\ListLowStockProductsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LowStockProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;

class LowStockProductController extends Controller
{
    public function index(LowStockProductRequest $request, ListLowStockProductsAction $action): JsonResponse
    {
        $threshold = $request->integer('threshold', config('store.low_stock_threshold'));

        return $this->successResponse(
            ProductResource::collection($action->execute($threshold)),
            meta: ['threshold' => $threshold],
        );
    }
}
