<?php

use function Pest\Laravel\post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;

it('uploads a new image with valid data', function () {
  Storage::fake('public');

  $file = UploadedFile::fake()->image('image.jpg');
  $filePath = $file->store('images', 'public');

  $category = Category::factory()->create();

  $data = [
    'path' => $filePath,
    'alt_text' => 'An image',
    'imageable_type' => Category::class,
    'imageable_id' => $category->id,
  ];

  $response = post('/api/images', $data);

  $response->assertCreated()
    ->assertJsonPath('path', $filePath)
    ->assertJsonPath('alt_text', 'An image')
    ->assertJsonPath('imageable_type', Category::class)
    ->assertJsonPath('imageable_id', $category->id);

  // Ensure the image is stored
  Storage::disk('public')->assertExists($filePath);
});
