<?php

namespace App\Http\Controllers\InstagramMedia;

use App\Http\Resources\InstagramMediaResource;
use App\Models\InstagramMedia;

class IndexInstagramMediaController
{
    public function __invoke()
    {
        $medias = InstagramMediaResource::collection(
            InstagramMedia::all()->sortBy('timestamp', SORT_REGULAR, true)->take(12)
        );

        return response()->json($medias);
    }
}
