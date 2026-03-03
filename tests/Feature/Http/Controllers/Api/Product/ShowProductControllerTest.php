<?php

use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\get;

it('returns a product with relationships', function () {
    $product = Product::factory()->create(['is_active' => true]);

    get("/api/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('id', $product->id)
        ->assertJsonPath('name', $product->name)
        ->assertJsonPath('category_name', $product->category->name);
});

it('returns 404 for nonexistent product', function () {
get('/api/products/9999')->assertNotFound();
    });

it('returns 404 for inactive product when unauthenticated', function () {
    $product = Product::factory()->create(['is_active' => false]);

    get("/api/products/{$product->id}")->assertNotFound();
});

it('returns inactive product for authenticated users', function () {
    $this->actingAs(User::factory()->create());

    $product = Product::factory()->create(['is_active' => false]);

    get("/api/products/{$product->id}")->assertOk();
});
