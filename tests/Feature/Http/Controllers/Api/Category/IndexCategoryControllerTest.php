<?php

use App\Models\Category;

use function Pest\Laravel\get;

it('returns all categories', function () {
    Category::factory()->count(3)->create();
    get('/api/categories')->assertOk()->assertJsonCount(3);
});

it('returns empty array when none exist', function () {
get('/api/categories')->assertOk()->assertJsonCount(0);
    });
