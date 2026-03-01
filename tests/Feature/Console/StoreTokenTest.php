<?php

use App\Services\StoreInitialToken\StoreInitialTokenService;

it('exchanges and stores the token successfully', function () {
    $mock = Mockery::mock(StoreInitialTokenService::class);
    $mock->shouldReceive('storeToken')->with('short-lived-token')->once()->andReturn(true);
    $this->app->instance(StoreInitialTokenService::class, $mock);

    $this->artisan('app:store-token', ['token' => 'short-lived-token'])
        ->expectsOutputToContain('Exchanging token for long-lived token...')
        ->expectsOutputToContain('Token stored successfully. It will expire in ~60 days.')
        ->assertExitCode(0);
});

it('returns failure when token exchange fails', function () {
    $mock = Mockery::mock(StoreInitialTokenService::class);
    $mock->shouldReceive('storeToken')->once()->andReturn(false);
    $this->app->instance(StoreInitialTokenService::class, $mock);

    $this->artisan('app:store-token', ['token' => 'invalid-token'])
        ->expectsOutputToContain('Failed to exchange or store the token. Check the logs for details.')
        ->assertExitCode(1);
});
