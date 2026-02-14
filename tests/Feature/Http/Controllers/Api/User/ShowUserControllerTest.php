<?php

use function Pest\Laravel\get;
use App\Models\User;

it('returns a user', function () {
    $user = User::factory()->create();

    get("/api/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('first_name', $user->first_name)
        ->assertJsonPath('last_name', $user->last_name)
        ->assertJsonPath('email', $user->email);
});

it('returns 404 for nonexistent user', function () {
    get('/api/users/9999')->assertNotFound();
});
