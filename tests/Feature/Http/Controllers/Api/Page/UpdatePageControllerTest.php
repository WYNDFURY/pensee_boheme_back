<?php

use App\Models\Page;
use App\Models\User;

use function Pest\Laravel\patchJson;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('updates a page slug', function () {
    $page = Page::factory()->create(['slug' => 'old-slug']);

    patchJson("/api/pages/{$page->slug}", ['slug' => 'new-slug'])
        ->assertOk()
        ->assertJsonPath('data.slug', 'new-slug');

    $this->assertDatabaseHas('pages', ['id' => $page->id, 'slug' => 'new-slug']);
});
