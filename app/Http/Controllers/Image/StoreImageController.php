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
      'imageable_type' => 'required|string|max:255',
      'imageable_id' => 'required|integer',
    ]);

    if ($request->hasFile('image')) {
      $path = $request->file('image')->store('images', 'public');

      $image = Image::create([
        'path' => $path,
        'alt_text' => $validated['alt_text'] ?? null,
        'imageable_type' => $validated['imageable_type'],
        'imageable_id' => $validated['imageable_id'],
      ]);

      return response()->json([
        'id' => $image->id,
        'path' => Storage::url($path),
        'alt_text' => $image->alt_text,
        'imageable_type' => $image->imageable_type,
        'imageable_id' => $image->imageable_id,
      ], 201);
    }

    return response()->json(['error' => 'Image upload failed'], 400);
  }
}
