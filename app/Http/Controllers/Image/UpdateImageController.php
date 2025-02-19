<?php

namespace App\Http\Controllers\Image;

use App\Models\Image;
use Illuminate\Http\Request;

class UpdateImageController
{
  public function __invoke(Request $request, Image $image)
  {
    $validated = $request->validate([
      'path' => 'required|string|max:255',
      'alt_text' => 'nullable|string|max:255',
      'imageable_type' => 'required|string|max:255',
      'imageable_id' => 'required|integer',
    ]);

    $image->update([
      'path' => $validated['path'],
      'alt_text' => $validated['alt_text'] ?? null,
      'imageable_type' => $validated['imageable_type'],
      'imageable_id' => $validated['imageable_id'],
    ]);

    return response()->json($image);
  }
}
