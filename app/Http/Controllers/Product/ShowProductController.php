<?php

namespace App\Http\Controllers\Product;

use App\Http\Resources\ProductResource;
use App\Models\Product;

class ShowProductController
{
    public function __invoke(int $product)
    {
        $query = Product::query();

        if (! auth('sanctum')->check()) {
            $query->active();
        }

        $product = $query->findOrFail($product);

        $product->load([
            'media' => fn ($q) => $q->where('collection_name', 'product_images'),
            'options',
            'category' => fn ($q) => $q->orderBy('order', 'asc'),
        ]);

        return new ProductResource($product);
    }
}
