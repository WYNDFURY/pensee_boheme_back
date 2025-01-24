<?php

use function Pest\Laravel\get;
use App\Models\Product;

it('returns a list of products', function () {
    Product::factory()->count(5)->create();

    $response = get('/api/products');
    // dd('response', $response->json());
    $response->assertOk()
        ->assertJsonCount(5);
});
