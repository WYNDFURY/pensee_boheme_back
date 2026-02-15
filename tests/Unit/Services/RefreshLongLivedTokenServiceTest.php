<?php

use App\Models\InstagramAccessToken;
use App\Services\RefreshLongLivedToken\RefreshLongLivedTokenService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    InstagramAccessToken::factory()->create(['id' => 1]);
    config([
        'tokenHandler.meta_app_id' => 'test-app-id',
        'tokenHandler.meta_app_secret' => 'test-secret',
    ]);
});

it('calls graph API to refresh token', function () {
    Http::fake([
        'graph.facebook.com/*' => Http::response([
            'access_token' => 'new-long-lived-token',
            'token_type' => 'bearer',
        ]),
    ]);

    $service = app(RefreshLongLivedTokenService::class);
    $result = $service->refreshLongLivedToken();

    expect($result['access_token'])->toBe('new-long-lived-token');
    Http::assertSent(fn ($request) => str_contains($request->url(), 'grant_type=fb_exchange_token'));
});
