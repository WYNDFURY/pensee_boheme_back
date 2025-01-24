<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdateProductController
{
    public function __invoke(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        $product->update($validated);

        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }

            // Save the new image
            $path = $request->file('image')->store('products', 'public');
            $product->images()->create([
                'path' => $path,
                'alt_text' => $request->input('alt_text', $product->name),
            ]);
        }

        return response()->json($product->load('images'));
    }
}
