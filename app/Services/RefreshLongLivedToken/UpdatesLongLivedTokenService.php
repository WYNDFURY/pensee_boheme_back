<?php

namespace App\Services\RefreshLongLivedToken;

use App\Models\InstagramAccessToken;

class UpdatesLongLivedTokenService
{
    public function updatesLongLivedToken($refreshedToken)
    {

        if ($refreshedToken) {
            // Assuming you have a model to update the token in the database
            $accessToken = InstagramAccessToken::first();
            $accessToken->updateOrInsert(
                ['id' => 1], // Assuming you want to update the first record
                [
                    'access_token' => encrypt($refreshedToken['access_token']),
                    'expires_at' => now()->addMonths(3)->format('Y-m-d H:i:s'),
                ]
            );
        }
    }
}
