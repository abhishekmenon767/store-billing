<?php

namespace App\Http\Controllers\Api;

use App\Actions\ListProductsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(ListProductsAction $action): JsonResponse
    {
        return $this->successResponse(ProductResource::collection($action->execute()));
    }
}
