<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\GalleryResource;
use App\Models\Gallery;

class ShowGalleryController
{
    public function __invoke(Gallery $gallery)
    {
        $gallery->load('media');

        return new GalleryResource($gallery);
    }
}
