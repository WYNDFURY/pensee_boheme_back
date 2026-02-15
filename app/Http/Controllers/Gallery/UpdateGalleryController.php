<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\GalleryResource;
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
      'images' => 'nullable|array|max:20',
      'images.*' => 'image|mimes:jpeg,png,webp,gif|max:10240',
    ]);

    $gallery->update(collect($validated)->except('images')->toArray());

    if ($request->hasFile('images')) {
      foreach ($request->file('images') as $file) {
        $gallery->addMedia($file)->toMediaCollection('gallery_images');
      }
    }

    $gallery->load('media');

    return response()->json([
      'message' => 'Gallery updated successfully',
      'gallery' => new GalleryResource($gallery),
    ]);
  }
}
