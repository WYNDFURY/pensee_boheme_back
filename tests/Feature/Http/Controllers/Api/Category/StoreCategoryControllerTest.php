<?php

use function Pest\Laravel\postJson;
use App\Models\Page;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('creates a category with valid data', function () {
    $page = Page::factory()->create();

    $response = postJson('/api/categories', [
        'name' => 'Accessoires',
        'slug' => 'accessoires',
        'description' => 'Floral accessories',
        'order' => 1,
        'page_id' => $page->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Accessoires')
        ->assertJsonPath('data.page_slug', $page->slug);

    $this->assertDatabaseHas('categories', [
        'name' => 'Accessoires',
        'page_id' => $page->id,
    ]);
});

it('rejects missing required fields', function () {
    postJson('/api/categories', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'slug', 'page_id']);
});

it('rejects nonexistent page_id', function () {
    postJson('/api/categories', [
        'name' => 'Test',
        'page_id' => 9999,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['page_id']);
});
