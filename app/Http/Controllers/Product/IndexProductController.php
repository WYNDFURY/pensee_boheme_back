<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;

class IndexProductController
{
    public function __invoke()
    {
        $products = Product::with('images')->get();

        return response()->json($products);
    }
}
