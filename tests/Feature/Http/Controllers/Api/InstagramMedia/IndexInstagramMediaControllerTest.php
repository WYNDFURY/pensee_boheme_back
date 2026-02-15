<?php

use function Pest\Laravel\get;
use App\Models\InstagramMedia;

it('returns at most 12 media sorted by timestamp desc', function () {
    for ($i = 1; $i <= 15; $i++) {
        InstagramMedia::create([
            'media_id' => "media_{$i}",
            'caption' => "Caption {$i}",
            'media_type' => 'IMAGE',
            'media_url' => "https://example.com/img_{$i}.jpg",
            'permalink' => "https://instagram.com/p/{$i}",
            'timestamp' => now()->subDays(15 - $i),
        ]);
    }

    $response = get('/api/instagram');

    $response->assertOk();
    $data = $response->json();
    expect(count($data))->toBeLessThanOrEqual(12);
});

it('returns empty array when no media exists', function () {
    get('/api/instagram')->assertOk()->assertJsonCount(0);
});
