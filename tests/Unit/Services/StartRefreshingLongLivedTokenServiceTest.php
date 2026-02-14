<?php

use App\Models\InstagramAccessToken;
use App\Services\RefreshLongLivedToken\StartRefreshingLongLivedTokenService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    InstagramAccessToken::factory()->create(['id' => 1]);
    config([
        'tokenHandler.meta_app_id' => 'test-app-id',
        'tokenHandler.meta_app_secret' => 'test-secret',
    ]);
});

it('orchestrates refresh and update', function () {
    Http::fake([
        'graph.facebook.com/*' => Http::response([
            'access_token' => 'refreshed-token',
            'token_type' => 'bearer',
        ]),
    ]);

    $service = app(StartRefreshingLongLivedTokenService::class);
    $service->startRefreshingLongLivedToken();

    $token = InstagramAccessToken::find(1);
    expect(decrypt($token->access_token))->toBe('refreshed-token');
});
