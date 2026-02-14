<?php

use App\Models\InstagramMedia;

it('can be created via factory', function () {
    $media = InstagramMedia::factory()->create();
    $this->assertDatabaseHas('instagram_media', ['media_id' => $media->media_id]);
});
