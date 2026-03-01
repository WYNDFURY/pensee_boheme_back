<?php

use function Pest\Laravel\get;
use App\Models\Gallery;

it('returns a gallery by slug', function () {
    $gallery = Gallery::factory()->create();

    get("/api/galleries/{$gallery->slug}")
        ->assertOk()
        ->assertJsonPath('slug', $gallery->slug)
        ->assertJsonPath('name', $gallery->name);
});

it('returns 404 for nonexistent slug', function () {
    get('/api/galleries/nonexistent-slug')->assertNotFound();
});
