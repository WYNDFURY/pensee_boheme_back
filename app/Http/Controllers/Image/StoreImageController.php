<?php

namespace App\Http\Controllers\Image;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreImageController
{
  public function __invoke(Request $request)
  {
    $validated = $request->validate([
      'image' => 'required|image|max:2048',
      'alt_text' => 'nullable|string|max:255',
      'category_id' => 'nullable|integer|exists:categories,id',
      'product_id' => 'nullable|integer|exists:products,id',
      'gallery_id' => 'nullable|integer|exists:galleries,id',
    ]);

    if ($request->hasFile('image')) {
      $path = $request->file('image')->store('images', 'public');

      $image = Image::create([
        'path' => $path,
        'alt_text' => $validated['alt_text'] ?? null,
        'category_id' => $validated['category_id'] ?? null,
        'product_id' => $validated['product_id'] ?? null,
        'gallery_id' => $validated['gallery_id'] ?? null,
      ]);

      return response()->json($image, 201);
    }

    return response()->json(['error' => 'Image upload failed'], 400);
  }
}
