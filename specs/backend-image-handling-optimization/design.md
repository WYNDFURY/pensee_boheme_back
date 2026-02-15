# Design — Backend Image Handling Optimization

## Overview

Replace single-size "optimized" WebP conversion (quality 90) with three responsive sizes (thumb, medium, large) at improved compression levels (quality 80-85). Add local image optimization via existing `spatie/image-optimizer` package (already installed v1.8.0) to achieve 20-30% total file size reduction while maintaining professional showcase quality.

**Core changes:**
1. Model media conversions: Replace single `optimized` with three size-specific conversions
2. MediaResource: Return all conversion URLs + metadata for responsive frontend implementation
3. Tests: Verify all conversions generated, MediaResource structure correct
4. No controller changes required (conversions transparent to upload logic)

## Architecture

```
┌─────────────────┐
│ Upload Request  │ (Product/Gallery Store/Update Controllers)
└────────┬────────┘
         │ Unchanged - existing validation/upload logic
         ▼
┌─────────────────────────────────────────────────┐
│ Spatie MediaLibrary FileAdder                   │
│ • addMediaFromRequest('image')                  │
│ • toMediaCollection('product_images')           │
└────────┬────────────────────────────────────────┘
         │ Triggers model's registerMediaConversions()
         ▼
┌─────────────────────────────────────────────────┐
│ Model: registerMediaConversions() [MODIFIED]    │
│ ┌─────────────────────────────────────────────┐ │
│ │ 1. thumb: 400×400 crop, WebP q80, optimize │ │
│ │ 2. medium: 1200px max, WebP q85, optimize  │ │
│ │ 3. large: 2000px max, WebP q85, optimize   │ │
│ └─────────────────────────────────────────────┘ │
│ • All nonQueued() - synchronous processing      │
│ • GD/ImageMagick: format, resize, quality       │
│ • image-optimizer: jpegoptim, pngquant, cwebp   │
└────────┬────────────────────────────────────────┘
         │ Conversions stored alongside original
         ▼
┌─────────────────────────────────────────────────┐
│ Storage Structure (storage/app/public/media/)   │
│ {model-id}/                                     │
│ ├── {uuid}.jpg           (original)             │
│ ├── conversions/                                │
│ │   ├── thumb-{uuid}.webp                       │
│ │   ├── medium-{uuid}.webp                      │
│ │   └── large-{uuid}.webp                       │
└────────┬────────────────────────────────────────┘
         │ API request loads media relation
         ▼
┌─────────────────────────────────────────────────┐
│ MediaResource: toArray() [MODIFIED]             │
│ Returns:                                        │
│ {                                               │
│   id, name, file_name, mime_type, size,         │
│   urls: {                                       │
│     thumb: "/.../thumb-{uuid}.webp",            │
│     medium: "/.../medium-{uuid}.webp",          │
│     large: "/.../large-{uuid}.webp",            │
│     original: "/.../​{uuid}.jpg"                 │
│   }                                             │
│ }                                               │
└────────┬────────────────────────────────────────┘
         │ JSON response to Nuxt frontend
         ▼
┌─────────────────────────────────────────────────┐
│ Nuxt Frontend (out of scope)                    │
│ • Uses thumb for list views                     │
│ • Uses medium for standard gallery display      │
│ • Uses large for lightbox/detail views          │
│ • Implements <picture> with srcset              │
└─────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Product Model (`app/Models/Product.php`)

**Changes to `registerMediaConversions()` method:**

```php
public function registerMediaConversions(?Media $media = null): void
{
    // Thumbnail - square crop for admin lists and preview grids
    $this->addMediaConversion('thumb')
        ->width(400)
        ->height(400)
        ->crop('crop-center')
        ->format('webp')
        ->quality(80)
        ->optimize()
        ->nonQueued();

    // Medium - standard gallery display, mobile/tablet viewport
    $this->addMediaConversion('medium')
        ->width(1200)
        ->format('webp')
        ->quality(85)
        ->optimize()
        ->nonQueued();

    // Large - desktop lightbox, high-quality showcase
    $this->addMediaConversion('large')
        ->width(2000)
        ->format('webp')
        ->quality(85)
        ->optimize()
        ->nonQueued();
}
```

**Technical notes:**
- Uses `->width()` instead of `->fit(Fit::Max, ...)` for simplicity (width constraint maintains aspect ratio)
- `crop('crop-center')` for thumb only — medium/large preserve aspect ratio
- `->optimize()` applies cwebp optimizer from config (lines 145-150: `-m 6 -pass 10 -mt -q 90`)
- Optimization gracefully degrades if cwebp binary unavailable (MediaLibrary catches exception)
- Remove existing `optimized` conversion entirely (breaking change handled via MediaResource fallback)

### 2. Gallery Model (`app/Models/Gallery.php`)

**Identical changes to `registerMediaConversions()` method:**

```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->width(400)
        ->height(400)
        ->crop('crop-center')
        ->format('webp')
        ->quality(80)
        ->optimize()
        ->nonQueued();

    $this->addMediaConversion('medium')
        ->width(1200)
        ->format('webp')
        ->quality(85)
        ->optimize()
        ->nonQueued();

    $this->addMediaConversion('large')
        ->width(2000)
        ->format('webp')
        ->quality(85)
        ->optimize()
        ->nonQueued();
}
```

**Rationale for identical conversions:**
- Consistent sizing across Product and Gallery models simplifies frontend implementation
- Admin UI can reuse components (both use same thumb size for lists)
- If future divergence needed (e.g., Gallery wants larger images), can be changed independently

### 3. MediaResource (`app/Http/Resources/MediaResource.php`)

**Current implementation (returns single URL):**
```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'url' => $this->getUrl('optimized'), // OLD - single size
    ];
}
```

**New implementation (returns all sizes + metadata):**
```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'file_name' => $this->file_name,
        'mime_type' => $this->mime_type,
        'size' => $this->size,
        'urls' => [
            'thumb' => $this->getUrlSafely('thumb'),
            'medium' => $this->getUrlSafely('medium'),
            'large' => $this->getUrlSafely('large'),
            'original' => $this->getUrl(),
        ],
    ];
}

/**
 * Get conversion URL with fallback to original if conversion unavailable.
 * Handles backwards compatibility for media uploaded before this change.
 */
private function getUrlSafely(string $conversion): string
{
    // Check if conversion exists before returning URL
    if ($this->hasGeneratedConversion($conversion)) {
        return $this->getUrl($conversion);
    }

    // Fallback to original for media without this conversion
    return $this->getUrl();
}
```

**Backwards compatibility strategy:**
- `getUrlSafely()` prevents 404s for media uploaded before this change
- Old media without new conversions will return original URL for all sizes
- Frontend can handle this gracefully (original may be larger but still works)
- Regeneration command (REQ-6) can fix old media: `php artisan media-library:regenerate`

**Breaking change mitigation:**
- Response structure changes from flat `url` field to nested `urls` object
- Frontend must update from `media.url` to `media.urls.medium` (or appropriate size)
- Coordinate deployment: backend first, then frontend update
- Consider adding deprecated `url` field temporarily: `'url' => $this->getUrlSafely('medium')` if zero-downtime required

### 4. Controllers (No Changes Required)

**Upload controllers remain unchanged:**
- `StoreProductController`: `$product->addMediaFromRequest('image')->toMediaCollection('product_images')`
- `UpdateProductController`: Same logic
- `StoreGalleryController`: Loop over `$request->file('images')` and `addMedia($file)`
- `UpdateGalleryController`: Same logic

**Why no changes:**
- Conversions defined in model, applied automatically by MediaLibrary
- FileAdder triggers `registerMediaConversions()` after upload
- All processing happens transparently within MediaLibrary lifecycle

### 5. Configuration (`config/media-library.php`)

**No changes required** — image optimizer already configured:

Lines 123-150 define optimizers including Cwebp (used for WebP optimization):
```php
Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
    '-m 6',      // slowest compression for best results
    '-pass 10',  // maximizing analysis passes
    '-mt',       // multithreading
    '-q 90',     // quality 90 (our conversions override to 80-85)
],
```

**Verification:**
- `->optimize()` method uses these configured optimizers
- If `cwebp` binary unavailable on server, MediaLibrary silently skips optimization (graceful degradation)
- Production deployment checklist must verify binary availability

## Data Models

### Media Model (Spatie)

No schema changes — uses existing `media` table:

```
media table (Spatie MediaLibrary):
- id
- model_type, model_id (polymorphic relation)
- uuid
- collection_name ('product_images', 'gallery_images')
- name, file_name
- mime_type, disk, size
- conversions_disk
- custom_properties (JSON)
- generated_conversions (JSON) - tracks which conversions exist
- responsive_images (JSON)
- order_column
- created_at, updated_at
```

**`generated_conversions` column after this change:**
```json
{
  "thumb": true,
  "medium": true,
  "large": true
}
```

**Old media (before this change):**
```json
{
  "optimized": true
}
```

This difference is why `getUrlSafely()` checks `hasGeneratedConversion()` before returning URL.

### API Response DTOs

**ProductResource / GalleryResource** (no changes):
- `media` field already uses `MediaResource::collection($this->getMedia('...'))`
- Change propagates automatically through MediaResource

**MediaResource response structure:**

**Before:**
```json
{
  "id": 1,
  "name": "photo.jpg",
  "url": "http://example.com/storage/media/1/conversions/optimized-abc123.webp"
}
```

**After:**
```json
{
  "id": 1,
  "name": "photo.jpg",
  "file_name": "abc123.jpg",
  "mime_type": "image/jpeg",
  "size": 2048576,
  "urls": {
    "thumb": "http://example.com/storage/media/1/conversions/thumb-abc123.webp",
    "medium": "http://example.com/storage/media/1/conversions/medium-abc123.webp",
    "large": "http://example.com/storage/media/1/conversions/large-abc123.webp",
    "original": "http://example.com/storage/media/1/abc123.jpg"
  }
}
```

## Error Handling

### 1. Optimization Binary Unavailable

**Scenario:** `cwebp` binary not installed on production server

**Behavior:**
- MediaLibrary `->optimize()` catches exception, logs warning, continues without optimization
- Conversions still generated (WebP format, quality 80-85) but without additional lossless optimization
- File size savings: ~15-20% instead of ~25-30%

**Detection:**
- Check production logs for image optimizer warnings after deployment
- Add to deployment checklist: verify `which cwebp` returns path

**Mitigation:**
- Document installation in deployment guide: `apt-get install webp` (Debian/Ubuntu)
- Consider adding health check endpoint that tests optimizer availability

### 2. Invalid Image Upload

**Scenario:** Corrupt file, unsupported format, oversized file

**Behavior:**
- Laravel validation catches at controller level (before MediaLibrary processing)
- Returns 422 Unprocessable Entity with validation errors
- Existing behavior unchanged

**Controller validation (already implemented):**
```php
'image' => 'nullable|image|mimes:jpeg,png,webp,gif|max:10240', // 10MB
```

### 3. Conversion Generation Failure

**Scenario:** Image manipulation error during conversion (e.g., corrupted source, insufficient memory)

**Behavior:**
- MediaLibrary catches exception during conversion
- `generated_conversions` JSON does not include failed conversion
- `getUrlSafely()` method returns original URL as fallback

**Logging:**
- MediaLibrary logs conversion errors via Laravel logger
- Monitor logs for `ConversionHasFailedEvent` patterns

### 4. Missing Conversions (Old Media)

**Scenario:** Media uploaded before this change, accessed after deployment

**Behavior:**
- `hasGeneratedConversion('medium')` returns false
- `getUrlSafely()` returns original URL
- Frontend receives valid URL (larger file, but functional)

**Resolution:**
- Run regeneration command: `php artisan media-library:regenerate`
- Scoped to specific model: `--model=App\\Models\\Product`
- Or scoped to collection: `--collection=product_images`

## Testing Strategy

### Unit Tests

**MediaResource URL Generation** (`tests/Unit/Http/Resources/MediaResourceTest.php`):

```php
it('returns all conversion URLs for media with conversions', function () {
    Storage::fake('media');
    $product = Product::factory()->create();
    $product->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('product_images');

    $media = $product->getFirstMedia('product_images');
    $resource = new MediaResource($media);
    $array = $resource->toArray(request());

    expect($array)->toHaveKeys(['id', 'name', 'file_name', 'mime_type', 'size', 'urls']);
    expect($array['urls'])->toHaveKeys(['thumb', 'medium', 'large', 'original']);
    expect($array['urls']['thumb'])->toContain('thumb-');
    expect($array['urls']['medium'])->toContain('medium-');
    expect($array['urls']['large'])->toContain('large-');
});

it('falls back to original URL when conversion missing', function () {
    Storage::fake('media');
    $product = Product::factory()->create();
    $media = $product->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('product_images');

    // Simulate old media by clearing generated_conversions
    $media->generated_conversions = [];
    $media->save();

    $resource = new MediaResource($media);
    $array = $resource->toArray(request());

    // All URLs should point to original
    expect($array['urls']['thumb'])->toEqual($array['urls']['original']);
    expect($array['urls']['medium'])->toEqual($array['urls']['original']);
});
```

### Feature Tests

**Product Image Upload Tests** (extend existing `StoreProductControllerTest.php`):

```php
it('generates three conversions when uploading product image', function () {
    Storage::fake('media');
    $category = Category::factory()->create();

    postJson('/api/products', [
        'name' => 'Test Product',
        'slug' => 'test-product-conversions',
        'category_id' => $category->id,
        'image' => UploadedFile::fake()->image('photo.jpg', 2000, 1500),
    ])->assertCreated();

    $product = Product::where('slug', 'test-product-conversions')->first();
    $media = $product->getFirstMedia('product_images');

    expect($media->hasGeneratedConversion('thumb'))->toBeTrue();
    expect($media->hasGeneratedConversion('medium'))->toBeTrue();
    expect($media->hasGeneratedConversion('large'))->toBeTrue();
});

it('returns all conversion URLs in product API response', function () {
    Storage::fake('media');
    $category = Category::factory()->create();

    $response = postJson('/api/products', [
        'name' => 'Test Product',
        'slug' => 'test-product-urls',
        'category_id' => $category->id,
        'image' => UploadedFile::fake()->image('photo.jpg', 1600, 1200),
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'product' => [
                'media' => [
                    '*' => ['id', 'name', 'file_name', 'mime_type', 'size', 'urls' => ['thumb', 'medium', 'large', 'original']]
                ]
            ]
        ]);

    $mediaUrls = $response->json('product.media.0.urls');
    expect($mediaUrls['thumb'])->toContain('thumb-');
    expect($mediaUrls['medium'])->toContain('medium-');
    expect($mediaUrls['large'])->toContain('large-');
});
```

**Gallery Image Upload Tests** (extend existing `StoreGalleryControllerTest.php`):

```php
it('generates conversions for all uploaded gallery images', function () {
    Storage::fake('media');

    $response = postJson('/api/galleries', [
        'name' => 'Test Gallery',
        'slug' => 'test-gallery-multi',
        'images' => [
            UploadedFile::fake()->image('photo1.jpg', 1920, 1080),
            UploadedFile::fake()->image('photo2.jpg', 1600, 900),
        ],
    ]);

    $gallery = Gallery::where('slug', 'test-gallery-multi')->first();
    $media = $gallery->getMedia('gallery_images');

    expect($media)->toHaveCount(2);
    foreach ($media as $item) {
        expect($item->hasGeneratedConversion('thumb'))->toBeTrue();
        expect($item->hasGeneratedConversion('medium'))->toBeTrue();
        expect($item->hasGeneratedConversion('large'))->toBeTrue();
    }
});
```

### Test Coverage Goals

- ✅ All three conversions generated for Product uploads
- ✅ All three conversions generated for Gallery uploads (each image in batch)
- ✅ MediaResource returns correct URL structure
- ✅ Fallback to original URL when conversion missing (backwards compatibility)
- ✅ Validation still rejects invalid files (existing tests continue passing)
- ✅ Update controller tests maintain existing assertions

**Test execution:**
```bash
php artisan test --filter=ProductController
php artisan test --filter=GalleryController
php artisan test --filter=MediaResource
```

## Performance Considerations

### Processing Time

**Current (single conversion):**
- 1 conversion: WebP q90, ~1-2 seconds per 5MB image

**After (three conversions + optimization):**
- 3 conversions: WebP q80-85 + optimize, ~3-5 seconds per 5MB image

**Mitigation strategies:**
1. Keep synchronous (nonQueued) for predictable admin UX — acceptable wait time for admin-only uploads
2. Upload frequency is low (admin only, not user-generated content)
3. Frontend loading indicator during upload (out of scope, but recommended)

**Batch uploads (Gallery with 10+ images):**
- Could take 30-60 seconds for 10×5MB images
- Consider future queued conversion implementation if becomes pain point (separate spec)

### Storage Impact

**Before:** 1 conversion per image
- Original: 5MB JPEG
- Optimized: ~2MB WebP (60% reduction)
- Total: 7MB per image

**After:** 3 conversions per image
- Original: 5MB JPEG
- Thumb: ~100KB WebP (small square)
- Medium: ~400KB WebP (1200px)
- Large: ~800KB WebP (2000px)
- Total: ~6.3MB per image

**Storage savings:** ~10% per image despite 3× conversions (better compression quality)

**Scalability:**
- 100 products × 2 images = 1.26GB (down from 1.4GB)
- 20 galleries × 15 images = 1.89GB (down from 2.1GB)
- Hosting plan: typically 20-50GB included, well within limits

### Bandwidth Savings (Frontend)

**Current:** Always serves 2MB optimized WebP

**After:**
- Mobile/list views: 100KB thumb (95% reduction)
- Tablet/standard: 400KB medium (80% reduction)
- Desktop/lightbox: 800KB large (60% reduction)

**Expected frontend performance gain:** 50-70% bandwidth reduction on average (varies by viewport distribution)

## Security Considerations

### No Changes to Attack Surface

- File upload validation unchanged (existing MIME type, size checks)
- No new endpoints or routes
- MediaLibrary continues storing files outside public web root
- Conversions inherit security properties of originals

### Image Processing Vulnerabilities

**Risk:** Malicious images exploiting GD/ImageMagick vulnerabilities

**Existing mitigation (unchanged):**
- Laravel validation limits file types to image/* MIME types
- File size limit: 10MB max (prevents resource exhaustion)
- MediaLibrary uses stable, maintained image processing libraries

**Additional consideration:**
- GD driver (default) has smaller attack surface than ImageMagick
- Config line 185: `'image_driver' => env('IMAGE_DRIVER', 'gd')`
- Production should verify GD driver used (not ImageMagick unless explicitly needed)

### Optimization Binary Security

**Risk:** Malicious `cwebp` binary executing arbitrary code

**Mitigation:**
- Install optimizer binaries via official package manager (apt/yum) not manual download
- Verify package signatures during installation
- Server-side only (admin uploads), not user-generated content

## Monitoring and Observability

### Metrics to Track

**1. Conversion generation success rate:**
- Monitor `generated_conversions` JSON in media table
- Alert if < 99% of uploads have all three conversions

**2. Storage growth:**
- Track `storage/app/public/media-library` directory size
- Expect ~10% decrease per image compared to current
- Alert if unexpected growth (indicates optimization failing)

**3. Upload processing time:**
- Add logging to controllers: time between upload start and response
- Expect 3-5 seconds per image
- Alert if > 10 seconds (indicates performance degradation)

**4. Optimizer availability:**
- Check logs for "cwebp not found" warnings after deployment
- Add health check: `shell_exec('which cwebp')` returns path

### Logging

**Existing MediaLibrary events to monitor:**
```php
MediaHasBeenAddedEvent       // Upload succeeded
ConversionHasBeenCompletedEvent  // Each conversion completed
ConversionHasFailedEvent     // Conversion error (investigate)
```

**Custom logging (optional):**
```php
// In controller after upload
Log::info('Media uploaded', [
    'model' => get_class($model),
    'collection' => 'product_images',
    'file_size' => $request->file('image')->getSize(),
    'conversions' => $media->generated_conversions,
    'processing_time_ms' => $processingTime,
]);
```

### Deployment Verification Checklist

Post-deployment verification steps:

1. **Upload test image via admin:** Verify 201 response, check response structure includes `urls` object
2. **Verify conversions generated:** Check `generated_conversions` in database for test upload
3. **Check storage directory:** Verify `conversions/thumb-*.webp`, `medium-*.webp`, `large-*.webp` files exist
4. **Test optimizer availability:** SSH to server, run `which cwebp`, confirm path returned
5. **Check logs:** Search for MediaLibrary errors, optimizer warnings
6. **Regenerate old media:** Run `php artisan media-library:regenerate --model=App\\Models\\Product` for existing products
7. **Frontend smoke test:** Verify Nuxt can fetch and display images at all sizes

### Rollback Plan

If issues detected post-deployment:

**Immediate (without code rollback):**
1. Old media still functional (fallback to original URLs)
2. Frontend can temporarily use `urls.original` for all sizes if needed

**Full rollback:**
1. Revert model `registerMediaConversions()` to single `optimized` conversion
2. Revert MediaResource to return flat `url` field
3. Redeploy backend
4. Old conversions still in storage (no data loss)

---

## Implementation Order

**Phase 1: Models & Resource (Core Changes)**
1. Update `Product::registerMediaConversions()` with three conversions
2. Update `Gallery::registerMediaConversions()` with three conversions
3. Update `MediaResource::toArray()` with new structure and `getUrlSafely()`

**Phase 2: Tests**
4. Add unit tests for `MediaResource` URL generation and fallback logic
5. Update/extend feature tests for Product upload with conversion verification
6. Update/extend feature tests for Gallery upload with conversion verification

**Phase 3: Documentation**
7. Update `CLAUDE.md` medialibrary-development skill reference with new conversion patterns
8. Document deployment checklist (optimizer binary verification)
9. Document regeneration command usage for existing media

**Phase 4: Deployment**
10. Deploy backend changes
11. Verify conversions generated on production
12. Run regeneration command for existing media
13. Coordinate frontend update to consume new `urls` structure

**Phase 5: Validation**
14. Monitor logs for optimizer warnings
15. Verify storage size decreased per expectations
16. Confirm frontend properly using responsive sizes

---

## References

- [Spatie Laravel MediaLibrary - Optimizing Converted Images](https://spatie.be/docs/laravel-medialibrary/v11/converting-images/optimizing-converted-images)
- [Spatie Image Optimizer Package](https://packagist.org/packages/spatie/image-optimizer)
- [5 Levels of Handling Images in Laravel](https://spatie.be/blog/five-levels-of-handling-images-in-laravel)
