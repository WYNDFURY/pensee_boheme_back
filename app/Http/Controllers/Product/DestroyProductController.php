<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;

class DestroyProductController
{
    public function __invoke(Product $product)
    {
        $medias = $product->getMedia('*');
        dd($medias);
        $product->delete();

        return response()->json(['message' => 'Product deleted'], 200);
    }
}
