<?php

use App\Services\RefreshLongLivedToken\StartRefreshingLongLivedTokenService;

it('runs the command successfully', function () {
    $mock = Mockery::mock(StartRefreshingLongLivedTokenService::class);
    $mock->shouldReceive('startRefreshingLongLivedToken')->once();
    $this->app->instance(StartRefreshingLongLivedTokenService::class, $mock);

    $this->artisan('app:refresh-token')
        ->assertExitCode(0);
});
