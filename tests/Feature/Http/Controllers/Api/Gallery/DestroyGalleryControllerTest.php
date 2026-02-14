<?php

use function Pest\Laravel\delete;
use App\Models\Gallery;

it('soft deletes a gallery', function () {
    $gallery = Gallery::factory()->create();

    delete("/api/galleries/{$gallery->slug}")->assertOk();

    $this->assertSoftDeleted($gallery);
});

it('returns 404 for nonexistent slug', function () {
    delete('/api/galleries/nonexistent-slug')->assertNotFound();
});
