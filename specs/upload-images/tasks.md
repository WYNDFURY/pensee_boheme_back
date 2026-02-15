# Upload Images — Implementation Plan

Ref: [requirements.md](requirements.md) | [design.md](design.md)

---

## Phase 1: Fix Model Media Registration

**Goal:** Correct the misuse of `registerMediaCollections()` vs `registerMediaConversions()` on Product and Gallery models. This is a prerequisite — without it, media collections are implicit and conversions may not trigger correctly.

**Verify:** Run existing test suite — all tests must still pass. The fix is non-breaking because media was being added with explicit collection names in resources already.

### Task 1.1: Fix Product model

**File:** `app/Models/Product.php`

- Rename `registerMediaCollections(?Media $media = null)` → split into two methods
- Add `registerMediaCollections(): void` — register `product_images` collection
- Add `registerMediaConversions(?Media $media = null): void` — move the `optimized` conversion here
- Remove the `?Media $media = null` param from `registerMediaCollections` (it doesn't take one)

**Test:** `php artisan test --filter=ProductControllerTest` — existing tests pass unchanged

### Task 1.2: Fix Gallery model

**File:** `app/Models/Gallery.php`

- Same split as Task 1.1, with `gallery_images` collection

**Test:** `php artisan test --filter=GalleryControllerTest` — existing tests pass unchanged

### Task 1.3: Run full test suite

**Command:** `php artisan test`

**Pass criteria:** All existing tests pass. No regressions.

---

## Phase 2: Product Image Upload (REQ-1, REQ-2)

**Goal:** `POST /api/products` and `PATCH /api/products/{product}` accept an optional `image` file, store it in the `product_images` collection, and return the product with media via `ProductResource`.

**Verify:** Create a product with an image via curl/Postman. Update a product to add another image. Confirm images appear in the JSON response with WebP URLs.

### Task 2.1: Update StoreProductController

**File:** `app/Http/Controllers/Product/StoreProductController.php`

- Add validation rule: `'image' => 'nullable|image|mimes:jpeg,png,webp,gif|max:10240'`
- Exclude `image` from `$validated` when calling `Product::create()`
- After creation: if `$request->hasFile('image')`, call `$product->addMediaFromRequest('image')->toMediaCollection('product_images')`
- Load media: `$product->load('media')`
- Return `new ProductResource($product)` instead of raw `$product`

**Tests** (add to `tests/Feature/Http/Controllers/Api/Product/StoreProductControllerTest.php`):
- `it('creates a product with an image')` — POST multipart with `UploadedFile::fake()->image('photo.jpg', 600, 400)`, assert 201, assert `product.media` has 1 item in response, assert `media` table has record linked to product
- `it('creates a product without an image')` — existing test still works (image is optional), verify `product.media` is empty array
- `it('rejects non-image file on product creation')` — POST with `UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')`, assert 422, assert `image` validation error

### Task 2.2: Update UpdateProductController

**File:** `app/Http/Controllers/Product/UpdateProductController.php`

- Add validation rule: `'image' => 'nullable|image|mimes:jpeg,png,webp,gif|max:10240'`
- Exclude `image` from `$validated` when calling `$product->update()`
- After update: if `$request->hasFile('image')`, call `$product->addMediaFromRequest('image')->toMediaCollection('product_images')`
- Load media: `$product->load('media')`
- Return `new ProductResource($product)` instead of raw `$product`

**Tests** (add to `tests/Feature/Http/Controllers/Api/Product/UpdateProductControllerTest.php`):
- `it('adds an image when updating a product')` — create product, POST to update endpoint with `_method=PATCH` and fake image, assert media count is 1
- `it('preserves existing images when updating without image')` — create product, add media to it manually, PATCH with only name change, assert media still present in response

### Task 2.3: Verify response shape

After Tasks 2.1–2.2, Store/Update controllers return `ProductResource` instead of raw model. Existing tests that assert on `product.name`, `product.slug` etc. need to be verified they still pass — `ProductResource` includes those same fields.

**Command:** `php artisan test --filter=ProductControllerTest`

---

## Phase 3: Gallery Image Upload (REQ-3, REQ-4)

**Goal:** `POST /api/galleries` and `PATCH /api/galleries/{gallery:slug}` accept an optional `images[]` file array, store each in the `gallery_images` collection, and return the gallery with media via `GalleryResource`.

**Verify:** Create a gallery with 3 images via curl/Postman. Update it to add 2 more. Confirm all 5 images appear in the JSON response.

### Task 3.1: Update StoreGalleryController

**File:** `app/Http/Controllers/Gallery/StoreGalleryController.php`

- Add validation rules: `'images' => 'nullable|array|max:20'` and `'images.*' => 'image|mimes:jpeg,png,webp,gif|max:10240'`
- Exclude `images` from `$validated` when calling `Gallery::create()`
- After creation: loop `$request->file('images')` and call `$gallery->addMedia($file)->toMediaCollection('gallery_images')` for each
- Load media: `$gallery->load('media')`
- Return `new GalleryResource($gallery)` instead of raw `$gallery`

**Tests** (add to `tests/Feature/Http/Controllers/Api/Gallery/StoreGalleryControllerTest.php`):
- `it('creates a gallery with multiple images')` — POST with 3 fake images in `images[]`, assert 201, assert `gallery.media` has 3 items
- `it('creates a gallery without images')` — existing test still works, verify `gallery.media` is empty array
- `it('rejects more than 20 images')` — POST with 21 fake images, assert 422, assert `images` validation error
- `it('rejects non-image file in batch')` — POST with 2 images + 1 PDF in `images[]`, assert 422

### Task 3.2: Update UpdateGalleryController

**File:** `app/Http/Controllers/Gallery/UpdateGalleryController.php`

- Add validation rules: `'images' => 'nullable|array|max:20'` and `'images.*' => 'image|mimes:jpeg,png,webp,gif|max:10240'`
- Exclude `images` from `$validated` when calling `$gallery->update()`
- After update: loop new files and add to `gallery_images` collection
- Load media: `$gallery->load('media')`
- Return `new GalleryResource($gallery)` instead of raw `$gallery`

**Tests** (add to `tests/Feature/Http/Controllers/Api/Gallery/UpdateGalleryControllerTest.php`):
- `it('appends images when updating a gallery')` — create gallery with 2 images manually, POST to update with `_method=PATCH` and 1 new image, assert 3 total media items
- `it('preserves existing images when updating without images')` — create gallery with media, PATCH with only name change, assert media count unchanged

### Task 3.3: Verify response shape and run full gallery tests

**Command:** `php artisan test --filter=GalleryControllerTest`

---

## Phase 4: Delete Media Endpoint (REQ-5)

**Goal:** `DELETE /api/media/{media}` removes a single media item (file + conversions + DB record). Auth required.

**Verify:** Upload an image to a product, note the media ID from the response, DELETE it, confirm it's gone from subsequent GET.

### Task 4.1: Create DestroyMediaController

**New file:** `app/Http/Controllers/Media/DestroyMediaController.php`

- Single-action invokable controller
- Type-hint `Media $media` (Spatie's `Spatie\MediaLibrary\MediaCollections\Models\Media`)
- Call `$media->delete()` — Spatie handles file + conversion cleanup
- Return `response()->json(['message' => 'Media deleted'])`

### Task 4.2: Create media route file

**New file:** `routes/api/media.php`

- `Route::middleware('auth:sanctum')->group(...)` wrapping `Route::delete('/media/{media}', DestroyMediaController::class)->name('media.destroy')`

**File modified:** `routes/api.php`

- Add `require __DIR__.'/api/media.php';` inside the `api.*` route group

### Task 4.3: Add auth protection test to ProtectedRoutesTest

**File:** `tests/Feature/Http/Controllers/Api/Auth/ProtectedRoutesTest.php`

- `it('rejects unauthenticated media deletion')` — `deleteJson('/api/media/1')->assertUnauthorized()`

### Task 4.4: Write DestroyMediaController tests

**New file:** `tests/Feature/Http/Controllers/Api/Media/DestroyMediaControllerTest.php`

- `it('deletes a media item')` — create product, add image via Spatie, DELETE the media by ID, assert 200, assert `media` table no longer has the record, assert product's media collection is empty
- `it('returns 404 for nonexistent media')` — DELETE `/api/media/99999`, assert 404

### Task 4.5: Run full test suite

**Command:** `php artisan test`

**Pass criteria:** All tests pass including new media delete tests.

---

## Phase 5: Verify Soft-Delete Media Behavior (REQ-6)

**Goal:** Confirm that soft-deleting a Product or Gallery preserves its media, and that Spatie handles cleanup correctly on force-delete.

**Verify:** Soft-delete a product with images, confirm media files still exist on disk. This phase is test-only — no code changes expected.

### Task 5.1: Write soft-delete media preservation tests

**File:** `tests/Feature/Http/Controllers/Api/Product/DestroyProductControllerTest.php` (add to existing)

- `it('preserves media when soft-deleting a product')` — create product with image, DELETE product, assert media record still exists in DB

**File:** `tests/Feature/Http/Controllers/Api/Gallery/DestroyGalleryControllerTest.php` (add to existing)

- `it('preserves media when soft-deleting a gallery')` — create gallery with image, DELETE gallery, assert media record still exists in DB

### Task 5.2: Run full test suite

**Command:** `php artisan test`

**Pass criteria:** All tests pass. Soft-delete behavior is confirmed.
