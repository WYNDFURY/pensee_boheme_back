<?php

use App\Models\Category;

use function Pest\Laravel\get;

it('returns a category', function () {
    $category = Category::factory()->create();

    get("/api/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('id', $category->id)
        ->assertJsonPath('name', $category->name);
});

it('returns 404 for nonexistent category', function () {
get('/api/categories/9999')->assertNotFound();
    });
