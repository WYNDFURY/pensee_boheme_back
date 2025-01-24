<?php

use function Pest\Laravel\get;
use App\Models\Category;

it('retrieves a specific category', function () {
  $category = Category::factory()->create();

  $response = get("/api/categories/{$category->id}");
  $response->assertOk()
    ->assertJsonPath('id', $category->id);
});
