<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\GalleryResource;
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
            'images' => 'nullable|array|max:20',
            'images.*' => 'image|mimes:jpeg,png,webp,gif|max:10240',
        ]);

        $data = collect($validated)->except('images')->toArray();

        if (! array_key_exists('order', $data)) {
            $data['order'] = (Gallery::max('order') ?? -1) + 1;
        }

        $gallery = Gallery::create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $gallery->addMedia($file)->toMediaCollection('gallery_images');
            }
        }

        $gallery->load('media');

        return response()->json([
            'message' => 'Gallery created successfully',
            'data' => new GalleryResource($gallery),
        ], 201);
    }
}
