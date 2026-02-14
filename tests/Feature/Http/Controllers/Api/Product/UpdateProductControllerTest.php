<?php

use function Pest\Laravel\patchJson;
use App\Models\Product;

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
