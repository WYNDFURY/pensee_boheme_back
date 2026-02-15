<?php

use function Pest\Laravel\delete;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('soft deletes a product', function () {
    $product = Product::factory()->create();

    delete("/api/products/{$product->id}")->assertOk();

    $this->assertSoftDeleted($product);
});

it('returns 404 for nonexistent product', function () {
    delete('/api/products/9999')->assertNotFound();
});

it('preserves media when soft-deleting a product', function () {
    Storage::fake('media');
    $product = Product::factory()->create();
    $product->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('product_images');

    $mediaId = $product->getFirstMedia('product_images')->id;

    delete("/api/products/{$product->id}")->assertOk();

    $this->assertSoftDeleted($product);
    $this->assertDatabaseHas('media', ['id' => $mediaId]);
});
