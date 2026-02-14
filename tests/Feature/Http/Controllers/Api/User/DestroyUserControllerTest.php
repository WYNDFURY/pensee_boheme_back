<?php

use function Pest\Laravel\delete;
use App\Models\User;

it('soft deletes a user', function () {
    $user = User::factory()->create();

    delete("/api/users/{$user->id}")->assertOk();

    $this->assertSoftDeleted($user);
});

it('returns 404 for nonexistent user', function () {
    delete('/api/users/9999')->assertNotFound();
});
