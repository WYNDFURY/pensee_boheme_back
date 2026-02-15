<?php

namespace App\Http\Controllers\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DestroyMediaController
{
    public function __invoke(Media $media)
    {
        $media->delete();

        return response()->json(['message' => 'Media deleted']);
    }
}
