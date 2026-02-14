<?php

use function Pest\Laravel\delete;
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
