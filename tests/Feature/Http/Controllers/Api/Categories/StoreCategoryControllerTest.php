<?php

use function Pest\Laravel\post;

it('creates a new category with valid data', function () {
  $data = [
    'name' => 'New Category',
  ];

  $response = post('/api/categories', $data);

  $response->assertCreated()
    ->assertJsonPath('name', 'New Category');

  // Ensure the category is created in the database
  $this->assertDatabaseHas('categories', [
    'name' => 'New Category',
  ]);
});
