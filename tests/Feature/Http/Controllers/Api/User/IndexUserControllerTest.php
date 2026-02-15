<?php

use function Pest\Laravel\get;
use App\Models\User;

it('returns all users', function () {
    User::factory()->count(3)->create();
    get('/api/users')->assertOk()->assertJsonCount(3);
});

it('returns empty array when no users exist', function () {
    get('/api/users')->assertOk()->assertJsonCount(0);
});
