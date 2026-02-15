# Upload Images — Design

## Overview

Add image upload support to the existing Product and Gallery CRUD endpoints, plus a new endpoint for deleting individual media items. The infrastructure (Spatie MediaLibrary, media table, disk config, conversions, API resources) already exists — the work is connecting file inputs to controllers and fixing model media registration.

## Architecture

```
Client (multipart/form-data)
  │
  ├─ POST   /api/products           → StoreProductController   (+ image)
  ├─ POST   /api/products/{id}      → UpdateProductController  (_method=PATCH + image)
  ├─ POST   /api/galleries          → StoreGalleryController   (+ images[])
  ├─ POST   /api/galleries/{slug}   → UpdateGalleryController  (_method=PATCH + images[])
  └─ DELETE /api/media/{media}      → DestroyMediaController
                │
                ▼
        Spatie MediaLibrary
          ├─ Stores original to `media` disk (storage/app/public/media)
          ├─ Runs `optimized` conversion (WebP, quality 90, sync)
          └─ Records in `media` table (polymorphic)
```

All write routes are behind `auth:sanctum`. Throttle `60,1` applies globally.

## Components and Interfaces

### Bug Fix: Model Media Registration

Both `Product` and `Gallery` models define their WebP conversion inside `registerMediaCollections()` instead of `registerMediaConversions()`. This must be corrected. Additionally, explicit media collections should be registered.

**Product model — updated methods:**
```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('product_images');
}

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('optimized')
        ->format('webp')
        ->quality(90)
        ->nonQueued();
}
```

**Gallery model — same fix:**
```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('gallery_images');
}

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('optimized')
        ->format('webp')
        ->quality(90)
        ->nonQueued();
}
```

### Modified Controllers

#### StoreProductController

Add image validation and media attachment after product creation.

```php
public function __invoke(Request $request)
{
    $validated = $request->validate([
        // ... existing rules unchanged ...
        'image' => 'nullable|image|mimes:jpeg,png,webp,gif|max:10240',
    ]);

    $product = Product::create(collect($validated)->except('image')->toArray());

    if ($request->hasFile('image')) {
        $product->addMediaFromRequest('image')->toMediaCollection('product_images');
    }

    $product->load('media');

    return response()->json([
        'message' => 'Product created successfully',
        'product' => new ProductResource($product),
    ], 201);
}
```

#### UpdateProductController

Same pattern. No route change needed — Laravel's `_method=PATCH` override on a `POST` request handles multipart natively.

```php
public function __invoke(Request $request, Product $product)
{
    $validated = $request->validate([
        // ... existing rules unchanged ...
        'image' => 'nullable|image|mimes:jpeg,png,webp,gif|max:10240',
    ]);

    $product->update(collect($validated)->except('image')->toArray());

    if ($request->hasFile('image')) {
        $product->addMediaFromRequest('image')->toMediaCollection('product_images');
    }

    $product->load('media');

    return response()->json([
        'message' => 'Product updated successfully',
        'product' => new ProductResource($product),
    ]);
}
```

#### StoreGalleryController

Accept `images[]` array field.

```php
public function __invoke(Request $request)
{
    $validated = $request->validate([
        // ... existing rules unchanged ...
        'images' => 'nullable|array|max:20',
        'images.*' => 'image|mimes:jpeg,png,webp,gif|max:10240',
    ]);

    $gallery = Gallery::create(collect($validated)->except('images')->toArray());

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $file) {
            $gallery->addMedia($file)->toMediaCollection('gallery_images');
        }
    }

    $gallery->load('media');

    return response()->json([
        'message' => 'Gallery created successfully',
        'gallery' => new GalleryResource($gallery),
    ], 201);
}
```

#### UpdateGalleryController

Same pattern — append images to existing collection.

```php
public function __invoke(Request $request, Gallery $gallery)
{
    $validated = $request->validate([
        // ... existing rules unchanged ...
        'images' => 'nullable|array|max:20',
        'images.*' => 'image|mimes:jpeg,png,webp,gif|max:10240',
    ]);

    $gallery->update(collect($validated)->except('images')->toArray());

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $file) {
            $gallery->addMedia($file)->toMediaCollection('gallery_images');
        }
    }

    $gallery->load('media');

    return response()->json([
        'message' => 'Gallery updated successfully',
        'gallery' => new GalleryResource($gallery),
    ]);
}
```

### New Controller: DestroyMediaController

New file: `app/Http/Controllers/Media/DestroyMediaController.php`

```php
<?php

namespace App\Http\Controllers\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DestroyMediaController
{
    public function __invoke(Media $media)
    {
        $media->delete();

        return response()->json(['message' => 'Media deleted']);
    }
}
```

### New Route File: routes/api/media.php

```php
<?php

use App\Http\Controllers\Media\DestroyMediaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/media/{media}', DestroyMediaController::class)->name('media.destroy');
});
```

Include from `routes/api.php`:
```php
require __DIR__.'/api/media.php';
```

### Response Changes

Store/Update controllers currently return raw model JSON. They will now return API Resources to include media in the response. This is a minor breaking change in response shape (fields go through resource transformation), but aligns with how Show/Index endpoints already work.

The existing `ProductResource`, `GalleryResource`, and `MediaResource` classes are unchanged — they already handle media serialization correctly.

## Data Models

No new migrations required. The `media` table already exists with the correct schema (polymorphic `model_type`/`model_id`, `collection_name`, `file_name`, `mime_type`, `size`, `order_column`, etc.).

**Media collections used:**
| Model | Collection Name | Type |
|-------|----------------|------|
| Product | `product_images` | Multiple files |
| Gallery | `gallery_images` | Multiple files |

**Conversion applied:**
| Name | Format | Quality | Queued |
|------|--------|---------|--------|
| `optimized` | WebP | 90 | No (sync) |

## Error Handling

| Scenario | Response | Status |
|----------|----------|--------|
| Validation failure (wrong mime, too large, too many files) | JSON with `errors` object per Laravel convention | 422 |
| Unauthenticated request | `{"message": "Unauthenticated."}` | 401 |
| Media not found on delete | Model binding returns 404 | 404 |
| Disk write failure | Let exception propagate — Laravel exception handler returns 500 | 500 |

Laravel's validation handles per-file errors with indexed keys (e.g., `images.2` must be an image). No custom error handling needed.

Validation occurs before model creation — if any file in a batch fails validation, the entire request is rejected atomically by Laravel's validator. No DB transaction wrapper needed for this since validation precedes all persistence.

## Testing Strategy

All tests use Pest PHP. Use `Illuminate\Http\UploadedFile::fake()` to create test files.

### Product upload tests (`tests/Feature/Http/Controllers/Api/Product/StoreProductControllerTest.php`)

Add to existing test file:
- `it('creates a product with an image')` — POST with fake image, assert 201, assert media in response, assert media record in DB
- `it('rejects non-image file on product creation')` — POST with a PDF, assert 422

### Product update tests (`tests/Feature/Http/Controllers/Api/Product/UpdateProductControllerTest.php`)

Add to existing test file:
- `it('adds an image when updating a product')` — PATCH with fake image, assert media count increased
- `it('preserves existing images when updating without image')` — add media, PATCH without image, assert media still present

### Gallery upload tests (`tests/Feature/Http/Controllers/Api/Gallery/StoreGalleryControllerTest.php`)

Add to existing test file:
- `it('creates a gallery with multiple images')` — POST with 3 fake images, assert 201, assert 3 media records
- `it('rejects more than 20 images')` — POST with 21 images, assert 422
- `it('rejects non-image file in batch')` — POST with 2 images + 1 text file, assert 422

### Gallery update tests (`tests/Feature/Http/Controllers/Api/Gallery/UpdateGalleryControllerTest.php`)

Add to existing test file:
- `it('appends images when updating a gallery')` — create with 2 images, PATCH with 1 more, assert 3 total

### Media delete tests (`tests/Feature/Http/Controllers/Api/Media/DestroyMediaControllerTest.php`)

New test file:
- `it('deletes a media item')` — create product with image, DELETE the media, assert media removed from DB
- `it('rejects unauthenticated media deletion')` — DELETE without auth, assert 401
- `it('returns 404 for nonexistent media')` — DELETE with bad ID, assert 404

### Fake disk usage

Tests should use `Storage::fake('media')` to prevent real file writes. Since the media disk is configured as `media` in `config/filesystems.php`, this will intercept all Spatie writes during tests.

## Performance Considerations

- **Sync conversions**: The `optimized` conversion runs `nonQueued()`. For a single product image this is fine (~1-2s). For gallery batch uploads of 20 images, this could take 20-40s. Acceptable for admin-only usage but worth monitoring.
- **Upload size limits**: PHP defaults `upload_max_filesize = 2M` and `post_max_size = 8M`. For 20 images at 10MB each, `post_max_size` needs to be at least 200MB. This must be configured in php.ini or `.htaccess`.
- **Memory**: GD image processing for large images can consume significant memory. PHP `memory_limit` should be at least 256MB.
- **Throttle**: The existing `throttle:60,1` is sufficient — admins won't hit 60 requests/minute during normal content management.

## Security Considerations

- **MIME validation**: `image` rule + `mimes:jpeg,png,webp,gif` ensures only real image files are accepted. Laravel checks actual file content, not just the extension.
- **File size**: Capped at 10MB per file via validation, 50MB hard limit via MediaLibrary config.
- **Path traversal**: Not possible — Spatie's `DefaultPathGenerator` uses media ID-based paths, no user input in file paths.
- **Auth**: All upload/delete endpoints require `auth:sanctum`. No public upload surface.
- **No direct execution**: Files stored in `storage/app/public/media` with symlink via `storage:link` — served as static files, not executed.

## Monitoring and Observability

No custom monitoring infrastructure needed for this feature. Rely on:
- Laravel's built-in exception logging for upload/conversion failures
- Standard HTTP status codes (201, 200, 422, 401, 404, 500) for API monitoring
- Disk usage can be tracked at the OS level if gallery uploads grow significantly over time
