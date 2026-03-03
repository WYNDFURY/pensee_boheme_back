<?php

use App\Models\Gallery;
use App\Models\User;

use function Pest\Laravel\get;

it('returns a published gallery by slug', function () {
    $gallery = Gallery::factory()->create(['is_published' => true]);

    get("/api/galleries/{$gallery->slug}")
        ->assertOk()
        ->assertJsonPath('slug', $gallery->slug)
        ->assertJsonPath('name', $gallery->name);
});

it('returns 404 for nonexistent slug', function () {
get('/api/galleries/nonexistent-slug')->assertNotFound();
    });

it('returns 404 for unpublished gallery when unauthenticated', function () {
    $gallery = Gallery::factory()->create(['is_published' => false]);

    get("/api/galleries/{$gallery->slug}")->assertNotFound();
});

it('returns unpublished gallery for authenticated users', function () {
    $this->actingAs(User::factory()->create());

    $gallery = Gallery::factory()->create(['is_published' => false]);

    get("/api/galleries/{$gallery->slug}")->assertOk();
});
