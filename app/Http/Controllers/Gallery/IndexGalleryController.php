<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\GalleryResource;
use App\Models\Gallery;

class IndexGalleryController
{
    public function __invoke()
    {
        $query = Gallery::with('media')->orderBy('order', 'asc');

        if (! auth('sanctum')->check()) {
            $query->published();
        }

        $galleries = $query->get();

        if (! auth('sanctum')->check()) {
            $galleries = $galleries->filter(fn ($gallery) => $gallery->getMedia('gallery_images')->isNotEmpty()
            )->values();
        }

        return GalleryResource::collection($galleries);
    }
}
