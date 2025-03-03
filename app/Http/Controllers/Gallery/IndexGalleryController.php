<?php

namespace App\Http\Controllers\Gallery;

use App\Models\Gallery;

class IndexGalleryController
{
    public function __invoke()
    {
        $galleries = Gallery::all();

        return response()->json($galleries);
    }
}
