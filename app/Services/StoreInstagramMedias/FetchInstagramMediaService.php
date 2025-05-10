<?php

namespace App\Services\StoreInstagramMedias;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchInstagramMediaService
{
    protected $accessToken;

    protected $appId;

    protected $appSecret;

    protected $instagramAccountId;

    public function __construct()
    {
        $this->appId = env('META_APP_ID');
        $this->appSecret = env('META_APP_SECRET');
        $this->accessToken = env('META_USER_ACCESS_TOKEN');
        $this->instagramAccountId = env('INSTAGRAM_ACCOUNT_ID');

    }

    public function fetchInstagramMedias()
    {

        Log::info('Fetching Instagram media...');

        $response = Http::get("https://graph.facebook.com/v22.0/{$this->instagramAccountId}/media", [
            'fields' => 'id,caption,media_type,media_url,permalink,timestamp',
            'limit' => 20,
            'access_token' => $this->accessToken,
        ]);

        $allMedias = $response->json('data', []);
        $filteredMedias = Arr::where($allMedias, function ($media) {
            return $media['media_type'] === 'IMAGE' || $media['media_type'] === 'CAROUSEL_ALBUM';
        });

        $filteredMedias = Arr::take($filteredMedias, 12);

        // dd($filteredMedias);

        if ($response->clientError()) {
            // Handle the error
            Log::error('Failed to fetch Instagram media: '.$response->body());

            return response()->json(['error' => 'Failed to fetch Instagram media'], 500);
        }

        Log::info('Instagram media fetched successfully');

        return $filteredMedias;

    }
}
