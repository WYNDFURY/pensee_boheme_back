<?php

use function Pest\Laravel\get;
use App\Models\Gallery;

it('excludes galleries without media', function () {
    Gallery::factory()->count(3)->create();
    get('/api/galleries')->assertOk()->assertJsonCount(0, 'data');
});

it('returns empty array when no galleries exist', function () {
    get('/api/galleries')->assertOk()->assertJsonCount(0, 'data');
});
