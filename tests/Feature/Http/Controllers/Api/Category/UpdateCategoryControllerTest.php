<?php

use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\patchJson;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('updates a category', function () {
    $category = Category::factory()->create();

    patchJson("/api/categories/{$category->id}", ['name' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated');

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated']);
});

it('allows partial update', function () {
    $category = Category::factory()->create();
    $originalName = $category->name;

    patchJson("/api/categories/{$category->id}", ['description' => 'New description'])
        ->assertOk();

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $originalName]);
});

it('rejects nonexistent page_id', function () {
    $category = Category::factory()->create();

    patchJson("/api/categories/{$category->id}", ['page_id' => 9999])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['page_id']);
});
