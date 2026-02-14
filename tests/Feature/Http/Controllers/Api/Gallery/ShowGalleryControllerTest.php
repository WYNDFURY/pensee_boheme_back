<?php

use function Pest\Laravel\get;
use App\Models\Gallery;

it('returns a gallery by slug', function () {
    $gallery = Gallery::factory()->create();

    get("/api/galleries/{$gallery->slug}")
        ->assertOk()
        ->assertJsonPath('data.slug', $gallery->slug)
        ->assertJsonPath('data.name', $gallery->name);
});

it('returns 404 for nonexistent slug', function () {
    get('/api/galleries/nonexistent-slug')->assertNotFound();
});
