<?php

use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('updates a product', function () {
    $product = Product::factory()->create();

    patchJson("/api/products/{$product->id}", [
        'name' => 'Updated Name',
    ])->assertOk()
        ->assertJsonPath('product.name', 'Updated Name');

    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Name']);
});

it('allows partial update', function () {
    $product = Product::factory()->create();
    $originalSlug = $product->slug;

    patchJson("/api/products/{$product->id}", ['name' => 'New Name'])
        ->assertOk();

    $this->assertDatabaseHas('products', ['id' => $product->id, 'slug' => $originalSlug]);
});

it('rejects duplicate slug on another product', function () {
    $product1 = Product::factory()->create(['slug' => 'taken-slug']);
    $product2 = Product::factory()->create();

    patchJson("/api/products/{$product2->id}", ['slug' => 'taken-slug'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

it('adds an image when updating a product', function () {
    Storage::fake('media');
    $product = Product::factory()->create();

    postJson("/api/products/{$product->id}", [
        '_method' => 'PATCH',
        'name' => 'With Image',
        'image' => UploadedFile::fake()->image('photo.jpg', 600, 400),
    ])->assertOk()
        ->assertJsonPath('product.name', 'With Image')
        ->assertJsonCount(1, 'product.media');

    expect($product->fresh()->getMedia('product_images'))->toHaveCount(1);
});

it('preserves existing images when updating without image', function () {
    Storage::fake('media');
    $product = Product::factory()->create();
    $product->addMedia(UploadedFile::fake()->image('existing.jpg'))
        ->toMediaCollection('product_images');

    patchJson("/api/products/{$product->id}", ['name' => 'No New Image'])
        ->assertOk()
        ->assertJsonCount(1, 'product.media');

    expect($product->fresh()->getMedia('product_images'))->toHaveCount(1);
});
