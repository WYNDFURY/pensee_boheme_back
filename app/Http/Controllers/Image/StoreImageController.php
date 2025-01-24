<?php

namespace App\Http\Controllers\Image;

use App\Models\Image;
use Illuminate\Http\Request;

class StoreImageController
{
  public function __invoke(Request $request)
  {
    $validated = $request->validate([
      'path' => 'required|string|max:255',
      'alt_text' => 'nullable|string|max:255',
    ]);

    $image = Image::create($validated);

    return response()->json($image, 201);
  }
}
