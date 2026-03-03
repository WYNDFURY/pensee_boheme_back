<?php

use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\get;

it('returns only active products for unauthenticated users', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    get('/api/products')->assertOk()->assertJsonCount(1);
});

it('returns all products for authenticated users', function () {
    $this->actingAs(User::factory()->create());

    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    get('/api/products')->assertOk()->assertJsonCount(2);
});

it('returns empty array when no active products exist', function () {
    Product::factory()->create(['is_active' => false]);

    get('/api/products')->assertOk()->assertJsonCount(0);
});
