<?php

use function Pest\Laravel\get;
use App\Models\Category;

it('returns a list of categories', function () {
  Category::factory()->count(5)->create();

  $response = get('/api/categories');
  $response->assertOk()
    ->assertJsonCount(5);
});
