<?php

use function Pest\Laravel\delete;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Gallery;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('soft deletes a gallery', function () {
    $gallery = Gallery::factory()->create();

    delete("/api/galleries/{$gallery->slug}")->assertOk();

    $this->assertSoftDeleted($gallery);
});

it('returns 404 for nonexistent slug', function () {
    delete('/api/galleries/nonexistent-slug')->assertNotFound();
});

it('preserves media when soft-deleting a gallery', function () {
    Storage::fake('media');
    $gallery = Gallery::factory()->create();
    $gallery->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('gallery_images');

    $mediaId = $gallery->getFirstMedia('gallery_images')->id;

    delete("/api/galleries/{$gallery->slug}")->assertOk();

    $this->assertSoftDeleted($gallery);
    $this->assertDatabaseHas('media', ['id' => $mediaId]);
});
