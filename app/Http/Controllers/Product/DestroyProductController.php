<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class DestroyProductController
{
    public function __invoke(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted'], 200);
    }
}
