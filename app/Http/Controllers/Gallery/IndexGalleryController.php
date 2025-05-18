<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\GalleriesResource;
use App\Models\Gallery;

class IndexGalleryController
{
    public function __invoke()
    {
        $galleries = GalleriesResource::collection(
            Gallery::with([
                'media' => function ($query) {
                    $query->where('collection_name', 'gallery_images');
                },
            ])->get()
        );

        return response()->json($galleries);
    }
}
