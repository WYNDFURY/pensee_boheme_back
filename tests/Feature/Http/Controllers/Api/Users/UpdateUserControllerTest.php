<?php

use function Pest\Laravel\patch;
use App\Models\User;

it('updates an existing user', function () {
  $user = User::factory()->create();

  $updatedData = [
    'first_name' => 'Updated First Name',
    'last_name' => 'Updated Last Name',
    'email' => 'updated@example.com',
  ];

  $response = patch("/api/users/{$user->id}", $updatedData);

  $response->assertOk()
    ->assertJsonPath('first_name', 'Updated First Name')
    ->assertJsonPath('last_name', 'Updated Last Name')
    ->assertJsonPath('email', 'updated@example.com');

  // Ensure the user is updated in the database
  $this->assertDatabaseHas('users', [
    'id' => $user->id,
    'first_name' => 'Updated First Name',
    'last_name' => 'Updated Last Name',
    'email' => 'updated@example.com',
  ]);
});
