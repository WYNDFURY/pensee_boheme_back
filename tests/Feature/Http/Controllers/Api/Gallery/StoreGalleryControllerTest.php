<?php

use function Pest\Laravel\postJson;
use App\Models\Gallery;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('creates a gallery with valid data', function () {
    $response = postJson('/api/galleries', [
        'name' => 'Bohème Chic',
        'slug' => 'boheme-chic',
        'description' => 'A beautiful gallery',
        'is_published' => true,
        'order' => 1,
    ]);

    $response->assertCreated()
        ->assertJsonPath('gallery.name', 'Bohème Chic')
        ->assertJsonPath('gallery.slug', 'boheme-chic');

    $this->assertDatabaseHas('galleries', ['slug' => 'boheme-chic']);
});

it('rejects missing required fields', function () {
    postJson('/api/galleries', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'slug']);
});

it('rejects duplicate slug', function () {
    Gallery::factory()->create(['slug' => 'existing']);

    postJson('/api/galleries', [
        'name' => 'Another',
        'slug' => 'existing',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});
