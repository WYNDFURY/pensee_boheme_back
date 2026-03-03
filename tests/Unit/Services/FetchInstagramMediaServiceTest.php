<?php

use App\Models\InstagramAccessToken;
use App\Services\StoreInstagramMedias\FetchInstagramMediaService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    InstagramAccessToken::factory()->create(['id' => 1]);
    config([
        'tokenHandler.meta_app_id' => 'test-app-id',
        'tokenHandler.meta_app_secret' => 'test-secret',
        'tokenHandler.instagram_account_id' => '12345',
    ]);
});

it('fetches and filters media by type', function () {
    Http::fake([
        'graph.facebook.com/*' => Http::response([
            'data' => [
                ['id' => '1', 'caption' => 'A', 'media_type' => 'IMAGE', 'media_url' => 'https://example.com/1.jpg', 'permalink' => 'https://ig.com/1', 'timestamp' => '2025-01-01T00:00:00+0000'],
                ['id' => '2', 'caption' => 'B', 'media_type' => 'VIDEO', 'media_url' => 'https://example.com/2.mp4', 'permalink' => 'https://ig.com/2', 'timestamp' => '2025-01-02T00:00:00+0000'],
                ['id' => '3', 'caption' => 'C', 'media_type' => 'CAROUSEL_ALBUM', 'media_url' => 'https://example.com/3.jpg', 'permalink' => 'https://ig.com/3', 'timestamp' => '2025-01-03T00:00:00+0000'],
            ],
        ]),
    ]);

    $service = app(FetchInstagramMediaService::class);
    $result = $service->fetchInstagramMedias();

    expect($result)->toHaveCount(2);
    $types = collect($result)->pluck('media_type')->all();
    expect($types)->each->not->toBe('VIDEO');
});

it('returns max 12 results', function () {
    $items = collect(range(1, 20))->map(fn ($i) => [
        'id' => (string) $i,
        'caption' => "Item $i",
        'media_type' => 'IMAGE',
        'media_url' => "https://example.com/$i.jpg",
        'permalink' => "https://ig.com/$i",
        'timestamp' => '2025-01-01T00:00:00+0000',
    ])->all();

    Http::fake([
        'graph.facebook.com/*' => Http::response(['data' => $items]),
    ]);

    $service = app(FetchInstagramMediaService::class);
    $result = $service->fetchInstagramMedias();

    expect($result)->toHaveCount(12);
});
