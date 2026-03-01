<?php

use function Pest\Laravel\patchJson;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('updates a user', function () {
    $user = User::factory()->create();

    patchJson("/api/users/{$user->id}", ['first_name' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.first_name', 'Updated');

    $this->assertDatabaseHas('users', ['id' => $user->id, 'first_name' => 'Updated']);
});

it('allows partial update', function () {
    $user = User::factory()->create();
    $originalFirstName = $user->first_name;

    patchJson("/api/users/{$user->id}", ['email' => 'new@example.com'])
        ->assertOk();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'first_name' => $originalFirstName,
        'email' => 'new@example.com',
    ]);
});

it('hashes password when updated', function () {
    $user = User::factory()->create();

    patchJson("/api/users/{$user->id}", ['password' => 'newpassword123'])
        ->assertOk();

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

it('rejects duplicate email on another user', function () {
    $user1 = User::factory()->create(['email' => 'first@example.com']);
    $user2 = User::factory()->create(['email' => 'second@example.com']);

    patchJson("/api/users/{$user2->id}", ['email' => 'first@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
