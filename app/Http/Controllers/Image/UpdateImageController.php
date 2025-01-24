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
    ]);

    $image->update($validated);

    return response()->json($image);
  }
}
