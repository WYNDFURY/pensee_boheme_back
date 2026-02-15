<?php

use function Pest\Laravel\get;
use App\Models\Category;

it('returns all categories', function () {
    Category::factory()->count(3)->create();
    get('/api/categories')->assertOk()->assertJsonCount(3);
});

it('returns empty array when none exist', function () {
    get('/api/categories')->assertOk()->assertJsonCount(0);
});
