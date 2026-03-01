<?php

namespace App\Services\RefreshLongLivedToken;

use App\Models\InstagramAccessToken;

class UpdatesLongLivedTokenService
{
    public function updatesLongLivedToken($refreshedToken)
    {

        if ($refreshedToken) {
            InstagramAccessToken::updateOrInsert(
                ['id' => 1],
                [
                    'access_token' => encrypt($refreshedToken['access_token']),
                    'expires_at' => now()->addMonths(3)->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]
            );
        }
    }
}
