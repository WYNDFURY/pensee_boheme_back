<?php

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

it('returns only published galleries with media for unauthenticated users', function () {
    Storage::fake('media');

    $published = Gallery::factory()->create(['is_published' => true]);
    $published->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('gallery_images');

    $unpublished = Gallery::factory()->create(['is_published' => false]);
    $unpublished->addMedia(UploadedFile::fake()->image('photo2.jpg'))
        ->toMediaCollection('gallery_images');

    get('/api/galleries')->assertOk()->assertJsonCount(1);
});

it('excludes published galleries without media for unauthenticated users', function () {
    Gallery::factory()->create(['is_published' => true]);

    get('/api/galleries')->assertOk()->assertJsonCount(0);
});

it('returns all galleries for authenticated users including unpublished and empty', function () {
    $this->actingAs(User::factory()->create());

    Gallery::factory()->count(3)->create();

    get('/api/galleries')->assertOk()->assertJsonCount(3);
});

it('returns galleries ordered by order column ascending', function () {
    Storage::fake('media');

    $second = Gallery::factory()->create(['is_published' => true, 'order' => 2]);
    $first = Gallery::factory()->create(['is_published' => true, 'order' => 1]);

    $second->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection('gallery_images');
    $first->addMedia(UploadedFile::fake()->image('b.jpg'))->toMediaCollection('gallery_images');

    $response = get('/api/galleries')->assertOk();
    expect($response->json('0.order'))->toBe(1);
    expect($response->json('1.order'))->toBe(2);
});

it('returns images_count for each gallery', function () {
    Storage::fake('media');

    $gallery = Gallery::factory()->create(['is_published' => true]);
    for ($i = 0; $i < 5; $i++) {
        $gallery->addMedia(UploadedFile::fake()->image("photo{$i}.jpg"))
            ->toMediaCollection('gallery_images');
    }

    $response = get('/api/galleries');
    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.images_count', 5)
        ->assertJsonCount(3, '0.media');
});

it('returns empty array when no galleries exist', function () {
get('/api/galleries')->assertOk()->assertJsonCount(0);
    });
