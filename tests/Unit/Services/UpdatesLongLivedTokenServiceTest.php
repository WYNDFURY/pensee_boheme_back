<?php

use App\Models\InstagramAccessToken;
use App\Services\RefreshLongLivedToken\UpdatesLongLivedTokenService;

it('updates token in database', function () {
    InstagramAccessToken::factory()->create(['id' => 1]);

    $service = app(UpdatesLongLivedTokenService::class);
    $service->updatesLongLivedToken(['access_token' => 'updated-token-value']);

    $token = InstagramAccessToken::find(1);
    expect(decrypt($token->access_token))->toBe('updated-token-value');
});
