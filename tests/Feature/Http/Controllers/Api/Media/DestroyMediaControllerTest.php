<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\deleteJson;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('deletes a media item', function () {
    Storage::fake('media');
    $product = Product::factory()->create();
    $product->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('product_images');

    $media = $product->getFirstMedia('product_images');

    deleteJson("/api/media/{$media->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Media deleted');

    $this->assertDatabaseMissing('media', ['id' => $media->id]);
    expect($product->fresh()->getMedia('product_images'))->toHaveCount(0);
});

it('returns 404 for nonexistent media', function () {
    deleteJson('/api/media/99999')->assertNotFound();
});
