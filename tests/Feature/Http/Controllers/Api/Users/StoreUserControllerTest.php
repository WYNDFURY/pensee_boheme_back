<?php

use function Pest\Laravel\post;

it('creates a new user with valid data', function () {
  $data = [
    'first_name' => 'New',
    'last_name' => 'User',
    'email' => 'newuser@example.com',
    'password' => 'password', // Ensure this is hashed in your actual implementation
  ];

  $response = post('/api/users', $data);

  $response->assertCreated()
    ->assertJsonPath('first_name', 'New')
    ->assertJsonPath('last_name', 'User')
    ->assertJsonPath('email', 'newuser@example.com');

  // Ensure the user is created in the database
  $this->assertDatabaseHas('users', [
    'first_name' => 'New',
    'last_name' => 'User',
    'email' => 'newuser@example.com',
  ]);
});
