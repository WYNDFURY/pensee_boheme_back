<?php

use function Pest\Laravel\patch;
use App\Models\Gallery;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('updates a gallery', function () {
    $gallery = Gallery::factory()->create();

    patch("/api/galleries/{$gallery->slug}", ['name' => 'Updated Name'])
        ->assertOk()
        ->assertJsonPath('gallery.name', 'Updated Name');

    $this->assertDatabaseHas('galleries', ['id' => $gallery->id, 'name' => 'Updated Name']);
});

it('allows partial update', function () {
    $gallery = Gallery::factory()->create();
    $originalName = $gallery->name;

    patch("/api/galleries/{$gallery->slug}", ['description' => 'New description'])
        ->assertOk();

    $this->assertDatabaseHas('galleries', ['id' => $gallery->id, 'name' => $originalName]);
});
