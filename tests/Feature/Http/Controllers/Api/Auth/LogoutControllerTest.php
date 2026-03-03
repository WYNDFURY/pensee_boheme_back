<?php

use App\Models\User;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('revokes current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    postJson('/api/logout', [], ['Authorization' => "Bearer $token"])
        ->assertOk()
        ->assertJsonPath('message', 'Logged out');

    // Reset auth guard cache so Sanctum re-checks the token
    app('auth')->forgetGuards();

    getJson('/api/user', ['Authorization' => "Bearer $token"])
        ->assertUnauthorized();
});

it('rejects unauthenticated logout', function () {
    postJson('/api/logout')->assertUnauthorized();
});
