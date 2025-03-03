<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;

class ShowProductController
{
    public function __invoke(Product $product)
    {

        return response()->json($product);
    }
}
