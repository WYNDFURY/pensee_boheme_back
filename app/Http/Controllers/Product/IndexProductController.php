<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;

class IndexProductController
{
    public function __invoke()
    {
        $products = Product::all();

        return response()->json($products);
    }
}
