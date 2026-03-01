<?php

use function Pest\Laravel\get;
use App\Models\Product;

it('returns a product with relationships', function () {
    $product = Product::factory()->create();

    get("/api/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('id', $product->id)
        ->assertJsonPath('name', $product->name)
        ->assertJsonPath('category_name', $product->category->name);
});

it('returns 404 for nonexistent product', function () {
    get('/api/products/9999')->assertNotFound();
});
