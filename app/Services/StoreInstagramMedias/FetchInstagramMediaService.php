<?php

namespace App\Services\StoreInstagramMedias;

use App\Models\InstagramAccessToken;
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
        $this->appId = config('tokenHandler.meta_app_id');
        $this->appSecret = config('tokenHandler.meta_app_secret');
        $this->accessToken = decrypt(InstagramAccessToken::where('id', 1)->first()->access_token);
        $this->instagramAccountId = config('tokenHandler.instagram_account_id');

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

        if ($response->clientError()) {
            // Handle the error
            Log::error('Failed to fetch Instagram media: '.$response->body());

            return response()->json(['error' => 'Failed to fetch Instagram media'], 500);
        }

        Log::info('Instagram media fetched successfully');

        return $filteredMedias;

    }
}
