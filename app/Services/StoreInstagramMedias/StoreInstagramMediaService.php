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
            if (empty($media['media_url'])) {
                Log::warning('Media URL is missing for media ID: '.$media['id']);

                continue; // Skip this media if the URL is missing
            }
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
