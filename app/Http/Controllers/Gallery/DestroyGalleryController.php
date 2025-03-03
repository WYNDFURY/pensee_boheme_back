<?php

namespace App\Http\Controllers\Gallery;

use App\Models\Gallery;

class DestroyGalleryController
{
    public function __invoke(Gallery $gallery)
    {
        $gallery->delete();

        return response()->json(['message' => 'Gallery deleted'], 200);
    }
}
