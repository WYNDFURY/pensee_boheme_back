<?php

namespace App\Services\StoreInstagramMedias;

use App\Models\InstagramMedia;
use Illuminate\Support\Facades\Log;

class StoreInstagramMediaService
{
    public function storeInstagramMedia($fetchedMedias)
    {
        Log::info('Storing Instagram media...');
        // Assuming $fetchedMedias is an array of media data
        foreach ($fetchedMedias as $media) {
            // clear all the existing instagram medias

            // Create a new InstagramMedia record
            InstagramMedia::create([
                'media_id' => $media['id'],
                'caption' => $media['caption'],
                'media_type' => $media['media_type'],
                'media_url' => $media['media_url'],
                'permalink' => $media['permalink'],
                'timestamp' => $media['timestamp'],
            ]);

        }
        Log::info('Instagram media stored successfully');

        return response()->json([
            'message' => 'Instagram media stored successfully',
        ], 200);
    }
}
