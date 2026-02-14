<?php

use App\Services\StoreInstagramMedias\StartStoringInstagramMediaService;

it('runs the command successfully', function () {
    $mock = Mockery::mock(StartStoringInstagramMediaService::class);
    $mock->shouldReceive('startStoringInstagramMedia')->once();
    $this->app->instance(StartStoringInstagramMediaService::class, $mock);

    $this->artisan('app:fns-instagram-medias')
        ->assertExitCode(0);
});
