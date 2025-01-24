<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class DestroyProductController
{
    public function __invoke(Product $product)
    {
        // Delete associated images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $product->delete();

        return response()->json(['message' => 'Product and its images deleted'], 200);
    }
}
