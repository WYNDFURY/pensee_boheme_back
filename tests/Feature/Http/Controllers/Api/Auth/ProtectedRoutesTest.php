<?php

use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\postJson;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\get;

it('rejects unauthenticated product creation', function () {
    postJson('/api/products', ['name' => 'Test'])->assertUnauthorized();
});

it('rejects unauthenticated category creation', function () {
    postJson('/api/categories', ['name' => 'Test'])->assertUnauthorized();
});

it('rejects unauthenticated page creation', function () {
    postJson('/api/pages', ['slug' => 'test'])->assertUnauthorized();
});

it('rejects unauthenticated gallery creation', function () {
    postJson('/api/galleries', ['name' => 'Test'])->assertUnauthorized();
});

it('rejects unauthenticated user creation', function () {
    postJson('/api/users', ['first_name' => 'Test'])->assertUnauthorized();
});

it('allows public product listing', function () {
    get('/api/products')->assertOk();
});

it('allows public contact form submission', function () {
    Mail::fake();

    postJson('/api/contact/creation', [
        'firstName' => 'Test',
        'lastName' => 'User',
        'email' => 'test@example.com',
        'phone' => '0612345678',
        'message' => 'Hello',
        'additional_info' => '',
    ])->assertStatus(200);
});

it('rejects unauthenticated media deletion', function () {
    deleteJson('/api/media/1')->assertUnauthorized();
});
