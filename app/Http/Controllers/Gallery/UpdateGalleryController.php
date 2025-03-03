<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;

class UpdateGalleryController
{
  public function __invoke(Request $request, Gallery $gallery)
  {
    $validated = $request->validate([
      'name' => 'sometimes|string|max:255',
      'slug' => 'sometimes|string|max:255|unique:galleries',
      'description' => 'nullable|string',
      'is_published' => 'sometimes|boolean',
      'order' => 'sometimes|integer',
    ]);

    // Update gallery with validated data
    $gallery->update($validated);

    return response()->json([
      'message' => 'Gallery updated successfully',
      'gallery' => $gallery,
    ]);
  }
}
