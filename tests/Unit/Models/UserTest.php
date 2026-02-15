<?php

use App\Models\User;

it('returns full name via accessor', function () {
    $user = User::factory()->create(['first_name' => 'Marie', 'last_name' => 'Dupont']);
    expect($user->full_name)->toBe('Marie Dupont');
});

it('uses soft deletes', function () {
    $user = User::factory()->create();
    $user->delete();
    $this->assertSoftDeleted('users', ['id' => $user->id]);
    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});
