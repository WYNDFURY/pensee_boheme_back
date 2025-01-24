<?php

use function Pest\Laravel\get;
use App\Models\User;

it('retrieves a specific user', function () {
  $user = User::factory()->create();

  $response = get("/api/users/{$user->id}");
  $response->assertOk()
    ->assertJsonPath('id', $user->id);
});
