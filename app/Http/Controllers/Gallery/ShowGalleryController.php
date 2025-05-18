<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\GalleryResource;
use App\Models\Gallery;

class ShowGalleryController
{
    public function __invoke(Gallery $gallery)
    {
        $gallery = GalleryResource::make(
            $gallery->load([
                'media' => function ($query) {
                    $query->where('collection_name', 'gallery_images');
                },
            ])
        );

        return new GalleryResource($gallery);
    }
}
