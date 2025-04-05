<?php

namespace App\Http\Controllers\Product;

use App\Http\Resources\ProductResource;
use App\Models\Product;

class ShowProductController
{
    public function __invoke(Product $product)
    {
        $product->load([
            'media' => function ($query) {
                $query->where('collection_name', 'product_images');
            },
            'options',
            'category' => function ($query) {
                $query->orderBy('order', 'asc');
            },
        ]);

        return new ProductResource($product);
    }
}
