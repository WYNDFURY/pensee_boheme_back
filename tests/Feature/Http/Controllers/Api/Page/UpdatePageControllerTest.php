<?php

use function Pest\Laravel\patch;
use App\Models\Page;

it('updates a page slug', function () {
    $page = Page::factory()->create(['slug' => 'old-slug']);

    patch("/api/pages/{$page->slug}", ['slug' => 'new-slug'])
        ->assertOk()
        ->assertJsonPath('page.slug', 'new-slug');

    $this->assertDatabaseHas('pages', ['id' => $page->id, 'slug' => 'new-slug']);
});
