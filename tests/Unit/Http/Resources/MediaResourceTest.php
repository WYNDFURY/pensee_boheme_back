<?php

use App\Http\Resources\MediaResource;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('returns all conversion URLs for media with conversions', function () {
    Storage::fake('media');

    $product = Product::factory()->create();
    $product->addMedia(UploadedFile::fake()->image('photo.jpg', 800, 600))
        ->toMediaCollection('product_images');

    $media = $product->getFirstMedia('product_images');
    $resource = new MediaResource($media);
    $array = $resource->toArray(request());

    expect($array)->toHaveKeys(['id', 'name', 'file_name', 'mime_type', 'size', 'urls']);
    expect($array['urls'])->toHaveKeys(['thumb', 'medium', 'large', 'original']);
});

it('falls back to original URL when conversion missing', function () {
    Storage::fake('media');

    $product = Product::factory()->create();
    $product->addMedia(UploadedFile::fake()->image('photo.jpg', 800, 600))
        ->toMediaCollection('product_images');

    $media = $product->getFirstMedia('product_images');

    // Simulate old media without new conversions
    $media->generated_conversions = [];
    $media->save();
    $media->refresh();

    $resource = new MediaResource($media);
    $array = $resource->toArray(request());

    expect($array['urls']['thumb'])->toEqual($array['urls']['original']);
    expect($array['urls']['medium'])->toEqual($array['urls']['original']);
    expect($array['urls']['large'])->toEqual($array['urls']['original']);
});

it('includes file metadata in response', function () {
    Storage::fake('media');

    $product = Product::factory()->create();
    $product->addMedia(UploadedFile::fake()->image('photo.jpg', 800, 600))
        ->toMediaCollection('product_images');

    $media = $product->getFirstMedia('product_images');
    $resource = new MediaResource($media);
    $array = $resource->toArray(request());

    expect($array['file_name'])->toBeString()->not->toBeEmpty();
    expect($array['mime_type'])->toStartWith('image/');
    expect($array['size'])->toBeInt()->toBeGreaterThan(0);
});
