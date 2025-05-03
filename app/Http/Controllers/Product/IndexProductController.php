<?php

namespace App\Http\Controllers\Product;

use App\Http\Resources\ProductResource;
use App\Models\Product;

class IndexProductController
{
    public function __invoke()
    {
        $products = ProductResource::collection(Product::with('category')->get());

        return response()->json($products);
    }
}
