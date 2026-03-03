<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\GalleryResource;
use App\Models\Gallery;

class ShowGalleryController
{
    public function __invoke(string $slug)
    {
        $query = Gallery::where('slug', $slug);

        if (! auth('sanctum')->check()) {
            $query->published();
        }

        $gallery = $query->firstOrFail();
        $gallery->load('media');

        return new GalleryResource($gallery);
    }
}
