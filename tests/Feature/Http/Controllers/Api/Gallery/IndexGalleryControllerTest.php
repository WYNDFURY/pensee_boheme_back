<?php

use function Pest\Laravel\get;
use App\Models\Gallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('excludes galleries without media', function () {
    Gallery::factory()->count(3)->create();
    get('/api/galleries')->assertOk()->assertJsonCount(0, 'data');
});

it('returns empty array when no galleries exist', function () {
    get('/api/galleries')->assertOk()->assertJsonCount(0, 'data');
});

it('returns images_count for each gallery', function () {
    Storage::fake('media');

    $gallery = Gallery::factory()->create();
    $gallery->addMedia(UploadedFile::fake()->image('photo1.jpg'))->toMediaCollection('gallery_images');
    $gallery->addMedia(UploadedFile::fake()->image('photo2.jpg'))->toMediaCollection('gallery_images');
    $gallery->addMedia(UploadedFile::fake()->image('photo3.jpg'))->toMediaCollection('gallery_images');
    $gallery->addMedia(UploadedFile::fake()->image('photo4.jpg'))->toMediaCollection('gallery_images');
    $gallery->addMedia(UploadedFile::fake()->image('photo5.jpg'))->toMediaCollection('gallery_images');

    $response = get('/api/galleries');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.images_count', 5)
        ->assertJsonCount(3, 'data.0.media'); // Media limited to 3 on index
});
