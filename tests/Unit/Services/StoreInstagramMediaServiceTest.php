<?php

use App\Services\StoreInstagramMedias\StoreInstagramMediaService;

it('creates instagram media records', function () {
    $medias = [
        ['id' => 'media-1', 'caption' => 'First', 'media_type' => 'IMAGE', 'media_url' => 'https://example.com/1.jpg', 'permalink' => 'https://ig.com/1', 'timestamp' => '2025-01-01T00:00:00+0000'],
        ['id' => 'media-2', 'caption' => 'Second', 'media_type' => 'CAROUSEL_ALBUM', 'media_url' => 'https://example.com/2.jpg', 'permalink' => 'https://ig.com/2', 'timestamp' => '2025-01-02T00:00:00+0000'],
    ];

    $service = app(StoreInstagramMediaService::class);
    $service->storeInstagramMedia($medias);

    $this->assertDatabaseCount('instagram_media', 2);
    $this->assertDatabaseHas('instagram_media', ['media_id' => 'media-1']);
    $this->assertDatabaseHas('instagram_media', ['media_id' => 'media-2']);
});
