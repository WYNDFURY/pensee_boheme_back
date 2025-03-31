<?php

namespace App\Http\Controllers\Product;

use App\Http\Resources\ProductIndexResource;
use App\Models\Product;

class IndexProductController
{
    public function __invoke()
    {
        $products = ProductIndexResource::collection(Product::with('category')->get());

        return response()->json($products);
    }
}
