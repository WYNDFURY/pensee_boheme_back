<?php

namespace App\Http\Controllers\InstagramMedia;

use App\Services\FetchingInstagramMediaService;

class IndexInstagramMediaController
{
    public function __invoke(FetchingInstagramMediaService $fetching)
    {
        $medias = $fetching->fetchInstagramMedia();

        return $medias;
    }
}
