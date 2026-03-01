<?php

use function Pest\Laravel\postJson;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('creates a user with valid data', function () {
    $response = postJson('/api/users', [
        'first_name' => 'Marie',
        'last_name' => 'Dupont',
        'email' => 'marie@example.com',
        'password' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.first_name', 'Marie')
        ->assertJsonPath('data.last_name', 'Dupont')
        ->assertJsonPath('data.email', 'marie@example.com');

    $this->assertDatabaseHas('users', [
        'first_name' => 'Marie',
        'email' => 'marie@example.com',
    ]);
});

it('hashes password on creation', function () {
    postJson('/api/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ])->assertCreated();

    $user = User::where('email', 'john@example.com')->first();
    expect(Hash::check('secret123', $user->password))->toBeTrue();
});

it('rejects missing required fields', function () {
    postJson('/api/users', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password']);
});

it('rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    postJson('/api/users', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'taken@example.com',
        'password' => 'password123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('rejects password under 8 characters', function () {
    postJson('/api/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'short',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('rejects invalid email format', function () {
    postJson('/api/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'not-an-email',
        'password' => 'password123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
