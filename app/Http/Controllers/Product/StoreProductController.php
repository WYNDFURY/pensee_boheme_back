<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use Illuminate\Http\Request;

class StoreProductController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'category_id' => $validated['category_id'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->images()->create([
                'path' => $path,
                'alt_text' => $request->input('alt_text', $validated['name']),
            ]);
        }

        return response()->json($product->load('images'), 201);
    }
}
