<?php

use function Pest\Laravel\delete;
use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('soft deletes a category', function () {
    $category = Category::factory()->create();

    delete("/api/categories/{$category->id}")->assertOk();

    $this->assertSoftDeleted($category);
});

it('returns 404 for nonexistent category', function () {
    delete('/api/categories/9999')->assertNotFound();
});
