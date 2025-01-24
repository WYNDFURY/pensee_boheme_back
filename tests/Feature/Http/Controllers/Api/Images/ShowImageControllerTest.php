<?php

use function Pest\Laravel\get;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('retrieves a specific image', function () {
  Storage::fake('public');

  $file = UploadedFile::fake()->image('image.jpg');
  $filePath = $file->store('images', 'public');

  $response = get("/api/images/{$filePath}");

  $response->assertOk()
    ->assertHeader('Content-Type', 'image/jpeg');
});
