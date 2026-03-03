<?php

namespace App\Http\Controllers\Product;

use App\Http\Resources\ProductResource;
use App\Models\Product;

class IndexProductController
{
    public function __invoke()
    {
        $query = Product::with('category');

        if (! auth('sanctum')->check()) {
            $query->active();
        }

        return ProductResource::collection($query->get());
    }
}
