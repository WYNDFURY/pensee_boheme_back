<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchingInstagramMediaService
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

    public function fetchInstagramMedia()
    {
        try {
            $response = Http::get("https://graph.facebook.com/v22.0/{$this->instagramAccountId}/media", [
                'fields' => 'id,caption,media_type,media_url,permalink,timestamp',
                'limit' => 10,
                'access_token' => $this->accessToken,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            // Handle the exception (e.g., log it, rethrow it, etc.)
            Log::error('Error fetching Instagram media: '.$e->getMessage());

            return null;

        }
    }
}
