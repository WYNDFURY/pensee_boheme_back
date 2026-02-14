<?php

use function Pest\Laravel\get;
use App\Models\Product;

it('returns all products', function () {
    Product::factory()->count(3)->create();
    get('/api/products')->assertOk()->assertJsonCount(3);
});

it('returns empty array when none exist', function () {
    get('/api/products')->assertOk()->assertJsonCount(0);
});
