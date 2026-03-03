<?php

use App\Models\Page;
use App\Models\User;

use function Pest\Laravel\delete;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('soft deletes a page', function () {
    $page = Page::factory()->create();

    delete("/api/pages/{$page->slug}")->assertOk();

    $this->assertSoftDeleted($page);
});

it('returns 404 for nonexistent slug', function () {
    delete('/api/pages/nonexistent-slug')->assertNotFound();
});
