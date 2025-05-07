<?php

namespace App\Services\RefreshLongLivedToken;

use App\Models\InstagramAccessToken;
use Illuminate\Support\Str;

class StartRefreshingLongLivedTokenService
{
    protected $refreshService;

    protected $updateService;

    public function __construct(RefreshLongLivedTokenService $refreshService, UpdatesLongLivedTokenService $updateService)
    {
        $this->refreshService = $refreshService;
        $this->updateService = $updateService;
    }

    public function startRefreshingLongLivedToken()
    {
        // $tokenBefore = InstagramAccessToken::where('id', 1)->first()->access_token;

        $refreshedToken = $this->refreshService->refreshLongLivedToken();
        $this->updateService->updatesLongLivedToken($refreshedToken);

        // $tokenAfter = InstagramAccessToken::where('id', 1)->first()->access_token;

        // dd([
        //     'old_token' => $tokenBefore,
        //     'new_token' => $tokenAfter,
        //     Str::contains($tokenBefore, $tokenAfter),
        // ]);

        return response()->json([
            'message' => 'Access token updated successfully',
        ], 200);
    }
}
