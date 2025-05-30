<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\GalleryResource;
use App\Models\Gallery;

class IndexGalleryController
{
    public function __invoke()
    {
        $galleries = Gallery::with('media')->get();

        $galleries = $galleries->filter(function ($gallery) {
            return $gallery->getMedia('gallery_images')->isNotEmpty();
        })->values();

        return GalleryResource::collection($galleries);
    }
}
