<?php

namespace App\Services\RefreshLongLivedToken;

use App\Models\InstagramAccessToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshLongLivedTokenService
{
    protected $accessToken;

    protected $appId;

    protected $appSecret;

    public function __construct()
    {
        $this->appId = config('tokenHandler.meta_app_id');
        $this->appSecret = config('tokenHandler.meta_app_secret');
        $this->accessToken = decrypt(InstagramAccessToken::first()->access_token);

    }

    public function refreshLongLivedToken()
    {

        $response = Http::get('https://graph.facebook.com/v22.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'fb_exchange_token' => $this->accessToken,
        ]);
        if ($response->clientError()) {
            Log::error('Failed to refresh long-lived token: '.$response->body());
        }

        return $response->json();
    }
}
