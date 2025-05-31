<?php

namespace App\Services\RefreshLongLivedToken;

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

        $refreshedToken = $this->refreshService->refreshLongLivedToken();
        $this->updateService->updatesLongLivedToken($refreshedToken);

        return response()->json([
            'message' => 'Access token updated successfully',
        ], 200);
    }
}
