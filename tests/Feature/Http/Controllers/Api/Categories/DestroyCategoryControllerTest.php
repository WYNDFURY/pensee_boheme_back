<?php

use function Pest\Laravel\delete;
use App\Models\Category;

it('deletes a category', function () {
  $category = Category::factory()->create();
  $response = delete("/api/categories/{$category->id}");
  $response->assertOk();

  $this->assertSoftDeleted($category);
});
