<?php

use function Pest\Laravel\patch;
use function Pest\Laravel\postJson;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Gallery;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('updates a gallery', function () {
    $gallery = Gallery::factory()->create();

    patch("/api/galleries/{$gallery->slug}", ['name' => 'Updated Name'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');

    $this->assertDatabaseHas('galleries', ['id' => $gallery->id, 'name' => 'Updated Name']);
});

it('allows partial update', function () {
    $gallery = Gallery::factory()->create();
    $originalName = $gallery->name;

    patch("/api/galleries/{$gallery->slug}", ['description' => 'New description'])
        ->assertOk();

    $this->assertDatabaseHas('galleries', ['id' => $gallery->id, 'name' => $originalName]);
});

it('appends images when updating a gallery', function () {
    Storage::fake('media');
    $gallery = Gallery::factory()->create();

    // Add 2 images manually
    $gallery->addMedia(UploadedFile::fake()->image('existing1.jpg'))
        ->toMediaCollection('gallery_images');
    $gallery->addMedia(UploadedFile::fake()->image('existing2.jpg'))
        ->toMediaCollection('gallery_images');

    // Update with 1 more image via POST + _method=PATCH
    postJson("/api/galleries/{$gallery->slug}", [
        '_method' => 'PATCH',
        'images' => [
            UploadedFile::fake()->image('new.jpg', 600, 400),
        ],
    ])->assertOk()
        ->assertJsonCount(3, 'data.media');

    expect($gallery->fresh()->getMedia('gallery_images'))->toHaveCount(3);
});

it('preserves existing images when updating without images', function () {
    Storage::fake('media');
    $gallery = Gallery::factory()->create();
    $gallery->addMedia(UploadedFile::fake()->image('existing.jpg'))
        ->toMediaCollection('gallery_images');

    patch("/api/galleries/{$gallery->slug}", ['name' => 'No New Images'])
        ->assertOk()
        ->assertJsonCount(1, 'data.media');

    expect($gallery->fresh()->getMedia('gallery_images'))->toHaveCount(1);
});
