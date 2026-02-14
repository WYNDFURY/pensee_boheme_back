<?php

use App\Models\InstagramAccessToken;
use App\Models\InstagramMedia;
use App\Services\StoreInstagramMedias\StartStoringInstagramMediaService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    InstagramAccessToken::factory()->create(['id' => 1]);
    config([
        'tokenHandler.meta_app_id' => 'test-app-id',
        'tokenHandler.meta_app_secret' => 'test-secret',
        'tokenHandler.instagram_account_id' => '12345',
    ]);
});

it('clears existing media and stores new batch', function () {
    InstagramMedia::factory()->count(3)->create();
    $this->assertDatabaseCount('instagram_media', 3);

    Http::fake([
        'graph.facebook.com/*' => Http::response([
            'data' => [
                ['id' => 'new-1', 'caption' => 'New', 'media_type' => 'IMAGE', 'media_url' => 'https://example.com/new.jpg', 'permalink' => 'https://ig.com/new', 'timestamp' => '2025-06-01T00:00:00+0000'],
                ['id' => 'new-2', 'caption' => 'New2', 'media_type' => 'IMAGE', 'media_url' => 'https://example.com/new2.jpg', 'permalink' => 'https://ig.com/new2', 'timestamp' => '2025-06-02T00:00:00+0000'],
            ],
        ]),
    ]);

    $service = app(StartStoringInstagramMediaService::class);
    $service->startStoringInstagramMedia();

    $this->assertDatabaseCount('instagram_media', 2);
    $this->assertDatabaseHas('instagram_media', ['media_id' => 'new-1']);
});
