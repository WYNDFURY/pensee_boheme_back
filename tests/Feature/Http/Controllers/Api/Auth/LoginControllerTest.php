<?php

use App\Models\User;
use function Pest\Laravel\postJson;

it('returns token with valid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret123'),
    ]);

    $response = postJson('/api/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'first_name', 'last_name', 'email'],
        ])
        ->assertJsonPath('user.email', $user->email);
});

it('rejects invalid password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret123'),
    ]);

    postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('rejects nonexistent email', function () {
    postJson('/api/login', [
        'email' => 'nobody@example.com',
        'password' => 'whatever',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('rejects missing fields', function () {
    postJson('/api/login', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('rate limits login attempts', function () {
    $user = User::factory()->create([
        'password' => bcrypt('secret123'),
    ]);

    for ($i = 0; $i < 5; $i++) {
        postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ]);
    }

    postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong',
    ])->assertStatus(429);
});
