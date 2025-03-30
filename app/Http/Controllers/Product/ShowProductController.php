<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use Illuminate\Support\Arr;

class ShowProductController
{
    public function __invoke(Product $product)
    {
        $options = $product->options()->get();

        $result = $product->toArray();

        // Add media to the result
        $result['media'] = $product->getMedia('product_images')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->getUrl('optimized'),
            ];
        });

        // Only add options if they exist
        if ($options->isNotEmpty()) {
            $result = Arr::add($result, 'options', $options);
        }

        return response()->json($result);
    }
}
