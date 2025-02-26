<?php

use function Pest\Laravel\get;

use App\Models\Category;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('retrieves a specific image', function () {
  Storage::fake('public');

  $file = UploadedFile::fake()->image('image.jpg');
  $filePath = $file->store('images', 'public');

  $category = Category::factory()->create();

  $image = Image::create([
    'path' => $filePath,
    'alt_text' => 'An image',
    'imageable_type' => Category::class,
    'imageable_id' => $category->id,
  ]);

  $response = get("/api/images/{$image->id}");

  $response->assertOk()
    ->assertJsonPath('path', $filePath)
    ->assertJsonPath('url', Storage::disk('public')->url($filePath))
    ->assertJsonPath('alt_text', 'An image')
    ->assertJsonPath('imageable_type', Category::class)
    ->assertJsonPath('imageable_id', $category->id);

  $response->assertJson([
    'imageable' => [
      'id' => $category->id,
    ]
  ]);
});
