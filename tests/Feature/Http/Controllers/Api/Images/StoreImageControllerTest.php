<?php

use function Pest\Laravel\post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads a new image with valid data', function () {
  Storage::fake('public');

  $file = UploadedFile::fake()->image('image.jpg');

  $data = [
    'image' => $file,
  ];

  $response = post('/api/images', $data);

  $response->assertCreated()
    ->assertJsonPath('file_name', 'image.jpg');

  // Ensure the image is stored
  Storage::disk('public')->assertExists('images/' . $file->hashName());
});
