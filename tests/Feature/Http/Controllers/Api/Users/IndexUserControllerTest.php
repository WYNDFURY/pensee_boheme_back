<?php

use function Pest\Laravel\get;
use App\Models\User;

it('returns a list of users', function () {
  User::factory()->count(5)->create();

  $response = get('/api/users');
  $response->assertOk()
    ->assertJsonCount(5);
});
