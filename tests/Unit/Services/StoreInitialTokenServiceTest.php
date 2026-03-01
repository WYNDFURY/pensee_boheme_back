<?php

use App\Models\InstagramAccessToken;
use App\Services\StoreInitialToken\StoreInitialTokenService;
use Illuminate\Support\Facades\Http;

it('exchanges a short-lived token and stores it in the database', function () {
    Http::fake([
        'graph.facebook.com/*' => Http::response([
            'access_token' => 'long-lived-token-abc',
            'token_type' => 'bearer',
        ], 200),
    ]);

    $service = app(StoreInitialTokenService::class);
    $result = $service->storeToken('short-lived-token');

    expect($result)->toBeTrue();
    expect(InstagramAccessToken::count())->toBe(1);
    expect(decrypt(InstagramAccessToken::first()->access_token))->toBe('long-lived-token-abc');
});

it('returns false when the api call fails', function () {
    Http::fake([
        'graph.facebook.com/*' => Http::response(['error' => 'invalid token'], 400),
    ]);

    $service = app(StoreInitialTokenService::class);
    $result = $service->storeToken('bad-token');

    expect($result)->toBeFalse();
    expect(InstagramAccessToken::count())->toBe(0);
});

it('returns false when the response is missing the access_token field', function () {
    Http::fake([
        'graph.facebook.com/*' => Http::response(['unexpected' => 'data'], 200),
    ]);

    $service = app(StoreInitialTokenService::class);
    $result = $service->storeToken('some-token');

    expect($result)->toBeFalse();
    expect(InstagramAccessToken::count())->toBe(0);
});
