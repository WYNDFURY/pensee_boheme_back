<?php

use App\Models\InstagramAccessToken;

it('can be created via factory', function () {
    $token = InstagramAccessToken::factory()->create();
    $this->assertDatabaseHas('instagram_access_tokens', ['id' => $token->id]);
});

it('uses soft deletes', function () {
    $token = InstagramAccessToken::factory()->create();
    $token->delete();
    $this->assertSoftDeleted('instagram_access_tokens', ['id' => $token->id]);
});
