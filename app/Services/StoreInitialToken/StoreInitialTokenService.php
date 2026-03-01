<?php

namespace App\Services\StoreInitialToken;

use App\Services\RefreshLongLivedToken\UpdatesLongLivedTokenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StoreInitialTokenService
{
    public function __construct(
        protected UpdatesLongLivedTokenService $updateService
    ) {}

    public function storeToken(string $shortLivedToken): bool
    {
        $response = Http::get('https://graph.facebook.com/v22.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('tokenHandler.meta_app_id'),
            'client_secret' => config('tokenHandler.meta_app_secret'),
            'fb_exchange_token' => $shortLivedToken,
        ]);

        if ($response->failed()) {
            Log::error('Failed to exchange token: '.$response->body());

            return false;
        }

        $tokenData = $response->json();

        if (! isset($tokenData['access_token'])) {
            Log::error('Token exchange response missing access_token field.');

            return false;
        }

        $this->updateService->updatesLongLivedToken($tokenData);
        Log::info('Initial Instagram token stored successfully.');

        return true;
    }
}
