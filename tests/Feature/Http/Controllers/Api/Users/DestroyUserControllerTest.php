<?php

use function Pest\Laravel\delete;
use App\Models\User;

it('deletes a user', function () {
  $user = User::factory()->create();
  $response = delete("/api/users/{$user->id}");
  $response->assertOk();

  $this->assertSoftDeleted($user);
});
