<?php

use App\Models\Page;
use App\Models\User;

use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('creates a page with valid data', function () {
    $response = postJson('/api/pages', ['slug' => 'new-page']);

    $response->assertCreated()
        ->assertJsonPath('data.slug', 'new-page');

    $this->assertDatabaseHas('pages', ['slug' => 'new-page']);
});

it('rejects missing slug', function () {
    postJson('/api/pages', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

it('rejects duplicate slug', function () {
    Page::factory()->create(['slug' => 'existing']);

    postJson('/api/pages', ['slug' => 'existing'])->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});
