<?php

use function Pest\Laravel\delete;

use App\Models\Category;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('deletes an image', function () {
  Storage::fake('public');

  $file = UploadedFile::fake()->image('image.jpg');
  $path = $file->store('images', 'public');

  $category = Category::factory()->create();

  $image = Image::create([
    'path' => $path,
    'alt_text' => 'An image',
    'category_id' => $category->id,
  ]);

  $response = delete("/api/images/{$image->id}");

  $response->assertOk();

  // Ensure the image is deleted
  Storage::disk('public')->assertMissing($path);
  $this->assertSoftDeleted('images', ['id' => $image->id]);
});
