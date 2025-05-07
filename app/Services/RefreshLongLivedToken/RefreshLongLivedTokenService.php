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
        $this->appId = env('META_APP_ID');
        $this->appSecret = env('META_APP_SECRET');
        // $this->accessToken = decrypt(InstagramAccessToken::where('id', 1)->first()->access_token);
        $this->accessToken = 'EAATv6D804BIBO4SZBAddWGrDZBmdKB45mrFhZBS69i55IJPKVocWe3u38eLgt3PyZBRfefAZAL1RI5onZCSaeN5ONxIrsZBm2GzDuhmymevRcxYocMTyBNXHTihoZCZC2Jamrhg94q1UhyORnEolF3pp8FCmbze3PPvAtSMwvJvIHUvJuZCVcqx8GTbAZDZD';

    }

    public function refreshLongLivedToken()
    {

        // dd([$this->accessToken, $this->appId, $this->appSecret]);
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
