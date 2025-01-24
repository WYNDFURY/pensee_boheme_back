<?php

use function Pest\Laravel\delete;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('deletes an image', function () {
  Storage::fake('public');

  $file = UploadedFile::fake()->image('image.jpg');
  $filePath = $file->store('images', 'public');

  $response = delete("/api/images/{$filePath}");

  $response->assertOk();

  // Ensure the image is deleted
  Storage::disk('public')->assertMissing($filePath);
});
