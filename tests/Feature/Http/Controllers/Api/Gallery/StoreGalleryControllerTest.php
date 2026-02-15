<?php

use function Pest\Laravel\postJson;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Gallery;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('creates a gallery with valid data', function () {
    $response = postJson('/api/galleries', [
        'name' => 'Bohème Chic',
        'slug' => 'boheme-chic',
        'description' => 'A beautiful gallery',
        'is_published' => true,
        'order' => 1,
    ]);

    $response->assertCreated()
        ->assertJsonPath('gallery.name', 'Bohème Chic')
        ->assertJsonPath('gallery.slug', 'boheme-chic');

    $this->assertDatabaseHas('galleries', ['slug' => 'boheme-chic']);
});

it('rejects missing required fields', function () {
    postJson('/api/galleries', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'slug']);
});

it('rejects duplicate slug', function () {
    Gallery::factory()->create(['slug' => 'existing']);

    postJson('/api/galleries', [
        'name' => 'Another',
        'slug' => 'existing',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

it('creates a gallery with multiple images', function () {
    Storage::fake('media');

    $response = postJson('/api/galleries', [
        'name' => 'Wedding Gallery',
        'slug' => 'wedding-gallery',
        'images' => [
            UploadedFile::fake()->image('photo1.jpg', 600, 400),
            UploadedFile::fake()->image('photo2.jpg', 600, 400),
            UploadedFile::fake()->image('photo3.jpg', 600, 400),
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('gallery.name', 'Wedding Gallery')
        ->assertJsonCount(3, 'gallery.media')
        ->assertJsonStructure(['gallery' => ['media' => [['urls' => ['thumb', 'medium', 'large', 'original']]]]]);

    $gallery = Gallery::where('slug', 'wedding-gallery')->first();
    expect($gallery->getMedia('gallery_images'))->toHaveCount(3);
});

it('generates conversions for all uploaded gallery images', function () {
    Storage::fake('media');

    postJson('/api/galleries', [
        'name' => 'Conversion Gallery',
        'slug' => 'conversion-gallery',
        'images' => [
            UploadedFile::fake()->image('photo1.jpg', 1920, 1080),
            UploadedFile::fake()->image('photo2.jpg', 1600, 900),
        ],
    ])->assertCreated();

    $gallery = Gallery::where('slug', 'conversion-gallery')->first();
    $media = $gallery->getMedia('gallery_images');

    expect($media)->toHaveCount(2);
    foreach ($media as $item) {
        expect($item->hasGeneratedConversion('thumb'))->toBeTrue();
        expect($item->hasGeneratedConversion('medium'))->toBeTrue();
        expect($item->hasGeneratedConversion('large'))->toBeTrue();
    }
});

it('creates a gallery without images', function () {
    postJson('/api/galleries', [
        'name' => 'Empty Gallery',
        'slug' => 'empty-gallery',
    ])->assertCreated()
        ->assertJsonCount(0, 'gallery.media');
});

it('rejects more than 20 images', function () {
    $images = [];
    for ($i = 0; $i < 21; $i++) {
        $images[] = UploadedFile::fake()->image("photo{$i}.jpg", 100, 100);
    }

    postJson('/api/galleries', [
        'name' => 'Too Many',
        'slug' => 'too-many',
        'images' => $images,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['images']);
});

it('rejects non-image file in batch', function () {
    postJson('/api/galleries', [
        'name' => 'Bad Batch',
        'slug' => 'bad-batch',
        'images' => [
            UploadedFile::fake()->image('good.jpg', 100, 100),
            UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['images.1']);
});
