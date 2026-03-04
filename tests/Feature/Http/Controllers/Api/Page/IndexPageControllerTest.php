<?php

use App\Models\Page;

use function Pest\Laravel\get;

it('returns all pages', function () {
    Page::factory()->count(3)->create();
    get('/api/pages')->assertOk()->assertJsonCount(3);
});

it('returns empty array when no pages exist', function () {
    get('/api/pages')->assertOk()->assertJsonCount(0);
});
