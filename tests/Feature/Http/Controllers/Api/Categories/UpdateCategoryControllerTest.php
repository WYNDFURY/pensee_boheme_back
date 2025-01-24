<?php

use function Pest\Laravel\patch;
use App\Models\Category;

it('updates an existing category', function () {
  $category = Category::factory()->create();

  $updatedData = [
    'name' => 'Updated Category Name',
  ];

  $response = patch("/api/categories/{$category->id}", $updatedData);

  $response->assertOk()
    ->assertJsonPath('name', 'Updated Category Name');

  // Ensure the category is updated in the database
  $this->assertDatabaseHas('categories', [
    'id' => $category->id,
    'name' => 'Updated Category Name',
  ]);
});
