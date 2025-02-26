<?php

use function Pest\Laravel\post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;

it('uploads a new image with valid data', function () {
  Storage::fake('public');

  $file = UploadedFile::fake()->image('image.jpg');

  $category = Category::factory()->create();

  $data = [
    'image' => $file,
    'alt_text' => 'An image',
    'imageable_type' => Category::class,
    'imageable_id' => $category->id,
  ];

  $response = post('/api/images', $data);

  $response->assertCreated()
    ->assertJsonPath('alt_text', 'An image')
    ->assertJsonPath('imageable_type', Category::class)
    ->assertJsonPath('imageable_id', $category->id);

  $path = $response->json('path');

  Storage::disk('public')->assertExists($path);
});
