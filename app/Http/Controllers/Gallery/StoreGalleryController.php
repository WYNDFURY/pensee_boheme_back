<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;

class StoreGalleryController extends Controller
{
  public function __invoke(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'slug' => 'required|string|max:255|unique:galleries',
      'description' => 'nullable|string',
      'is_published' => 'sometimes|boolean',
      'order' => 'sometimes|integer',
    ]);

    $gallery = Gallery::create($validated);

    return response()->json([
      'message' => 'Gallery created successfully',
      'gallery' => $gallery,
    ], 201);
  }
}
