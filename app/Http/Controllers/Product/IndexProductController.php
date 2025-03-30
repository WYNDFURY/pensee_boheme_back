<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;

class IndexProductController
{
    public function __invoke()
    {
        $products = Product::all()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->getFirstMediaUrl('product_images', 'webp'),
                'category_id' => $product->category_id,
            ];
        });

        return response()->json($products);
    }
}
