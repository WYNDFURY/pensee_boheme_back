<?php

use function Pest\Laravel\get;
use App\Models\Product;

it('returns a product with relationships', function () {
    $product = Product::factory()->create();

    get("/api/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.name', $product->name)
        ->assertJsonPath('data.category_id', $product->category_id);
});

it('returns 404 for nonexistent product', function () {
    get('/api/products/9999')->assertNotFound();
});
