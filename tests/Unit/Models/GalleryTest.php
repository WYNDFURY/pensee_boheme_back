<?php

use App\Models\Gallery;

it('uses soft deletes', function () {
    $gallery = Gallery::factory()->create();
    $gallery->delete();
    $this->assertSoftDeleted('galleries', ['id' => $gallery->id]);
});

it('casts is_published to boolean', function () {
    $gallery = Gallery::factory()->create(['is_published' => 1]);
    expect($gallery->is_published)->toBeTrue();
});
