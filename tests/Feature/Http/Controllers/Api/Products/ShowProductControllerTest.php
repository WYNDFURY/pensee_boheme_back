<?php

use function Pest\Laravel\get;
use App\Models\Product;

it('retrieves a specific product', function () {
    $product = Product::factory()->create();

    $response = get("/api/products/{$product->id}");
    $response->assertOk()
        ->assertJsonPath('id', $product->id);
});
