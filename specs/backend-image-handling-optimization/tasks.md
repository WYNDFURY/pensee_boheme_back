# Implementation Tasks — Backend Image Handling Optimization

## Phase 1: Update Model Media Conversions

**Goal:** Replace single "optimized" conversion with three responsive sizes (thumb, medium, large) in Product and Gallery models.

**Verification:** Upload an image via existing controller, verify three conversion files generated in storage and `generated_conversions` JSON contains all three keys.

### Tasks

#### Task 1.1: Update Product model conversions
- **File:** `app/Models/Product.php`
- **Action:** Replace `registerMediaConversions()` method
- **Changes:**
  - Remove single `optimized` conversion
  - Add `thumb` conversion: 400×400 crop, WebP quality 80, optimize, nonQueued
  - Add `medium` conversion: 1200px width, WebP quality 85, optimize, nonQueued
  - Add `large` conversion: 2000px width, WebP quality 85, optimize, nonQueued
- **Validation:** Manual upload test via Postman/curl to `/api/products` with image file

#### Task 1.2: Update Gallery model conversions
- **File:** `app/Models/Gallery.php`
- **Action:** Replace `registerMediaConversions()` method with identical conversions as Product
- **Changes:** Same three conversions (thumb, medium, large) with identical specifications
- **Validation:** Manual upload test via Postman/curl to `/api/galleries` with multiple image files

#### Task 1.3: Verify conversions in storage
- **Action:** After manual uploads, inspect `storage/app/public/media-library/` directory
- **Expected:**
  - Three WebP files in `conversions/` subdirectory for each upload
  - Filenames: `thumb-{uuid}.webp`, `medium-{uuid}.webp`, `large-{uuid}.webp`
  - File sizes: thumb < medium < large (progressive sizes)
- **Database check:** Query `media` table, verify `generated_conversions` JSON: `{"thumb":true,"medium":true,"large":true}`

---

## Phase 2: Update MediaResource Response Structure

**Goal:** Transform MediaResource to return all conversion URLs in nested `urls` object with backwards compatibility fallback.

**Verification:** API response includes `urls` object with four keys (thumb, medium, large, original), all returning valid URLs.

### Tasks

#### Task 2.1: Add getUrlSafely private method
- **File:** `app/Http/Resources/MediaResource.php`
- **Action:** Add private helper method for conversion URL retrieval with fallback
- **Implementation:**
  ```php
  private function getUrlSafely(string $conversion): string
  {
      if ($this->hasGeneratedConversion($conversion)) {
          return $this->getUrl($conversion);
      }
      return $this->getUrl(); // fallback to original
  }
  ```
- **Purpose:** Handles old media uploaded before this change (no new conversions)

#### Task 2.2: Update toArray method structure
- **File:** `app/Http/Resources/MediaResource.php`
- **Action:** Replace existing flat structure with nested URLs object
- **Changes:**
  - Add fields: `file_name`, `mime_type`, `size`
  - Replace single `url` with `urls` object containing:
    - `thumb`: `$this->getUrlSafely('thumb')`
    - `medium`: `$this->getUrlSafely('medium')`
    - `large`: `$this->getUrlSafely('large')`
    - `original`: `$this->getUrl()`
- **Validation:** Manual API test, verify JSON structure matches design spec

#### Task 2.3: Test backwards compatibility manually
- **Action:** Create test media without new conversions (simulate old upload)
- **Method:**
  - Upload image
  - Manually edit database: set `generated_conversions = '{"optimized":true}'`
  - Fetch via API
  - Verify all URLs in `urls` object point to original (no 404s)

---

## Phase 3: Add MediaResource Unit Tests

**Goal:** Comprehensive unit test coverage for MediaResource transformation logic and fallback behavior.

**Verification:** All MediaResource tests pass (`php artisan test --filter=MediaResource`).

### Tasks

#### Task 3.1: Create MediaResource test file
- **File:** `tests/Unit/Http/Resources/MediaResourceTest.php` (new directory + file)
- **Action:** Create test file with Pest syntax
- **Setup:**
  ```php
  <?php

  use App\Http\Resources\MediaResource;
  use App\Models\Product;
  use Illuminate\Http\UploadedFile;
  use Illuminate\Support\Facades\Storage;
  ```

#### Task 3.2: Test complete conversion URL structure
- **Test:** `it('returns all conversion URLs for media with conversions')`
- **Setup:**
  - Fake storage
  - Create Product with uploaded image
  - Get first media item
- **Assertions:**
  - Response has keys: `id`, `name`, `file_name`, `mime_type`, `size`, `urls`
  - `urls` has keys: `thumb`, `medium`, `large`, `original`
  - Each URL contains expected conversion name in path

#### Task 3.3: Test fallback to original when conversions missing
- **Test:** `it('falls back to original URL when conversion missing')`
- **Setup:**
  - Fake storage
  - Create Product with uploaded image
  - Manually clear `generated_conversions` on media model (simulate old media)
- **Assertions:**
  - `urls.thumb` equals `urls.original`
  - `urls.medium` equals `urls.original`
  - `urls.large` equals `urls.original`
  - No 404 errors or null values

#### Task 3.4: Test metadata fields present
- **Test:** `it('includes file metadata in response')`
- **Setup:**
  - Create Product with uploaded image
  - Get MediaResource array
- **Assertions:**
  - `file_name` is string and not empty
  - `mime_type` starts with 'image/'
  - `size` is integer > 0

---

## Phase 4: Update Product Upload Feature Tests

**Goal:** Extend existing Product controller tests to verify three conversions generated and MediaResource structure correct.

**Verification:** All Product controller tests pass including new conversion checks.

### Tasks

#### Task 4.1: Add test for three conversions generated
- **File:** `tests/Feature/Http/Controllers/Api/Product/StoreProductControllerTest.php`
- **Test:** `it('generates three conversions when uploading product image')`
- **Setup:**
  - Fake storage
  - Create category
  - POST to `/api/products` with 2000×1500 image
- **Assertions:**
  - Product created successfully
  - `$media->hasGeneratedConversion('thumb')` returns true
  - `$media->hasGeneratedConversion('medium')` returns true
  - `$media->hasGeneratedConversion('large')` returns true

#### Task 4.2: Add test for MediaResource URL structure in response
- **File:** `tests/Feature/Http/Controllers/Api/Product/StoreProductControllerTest.php`
- **Test:** `it('returns all conversion URLs in product API response')`
- **Setup:**
  - Fake storage
  - Create category
  - POST to `/api/products` with image
- **Assertions:**
  - Response status 201
  - JSON structure includes `product.media[0].urls` with all four keys
  - Each URL contains expected conversion name substring

#### Task 4.3: Update existing image upload test assertions
- **File:** `tests/Feature/Http/Controllers/Api/Product/StoreProductControllerTest.php`
- **Test:** Update `it('creates a product with an image')` test
- **Changes:**
  - Keep existing assertion: `->assertJsonCount(1, 'product.media')`
  - Add assertion: `->assertJsonStructure(['product' => ['media' => [['urls' => ['thumb', 'medium', 'large', 'original']]]]])`

#### Task 4.4: Add test for UpdateProductController conversions
- **File:** `tests/Feature/Http/Controllers/Api/Product/UpdateProductControllerTest.php`
- **Test:** Verify existing `it('adds image when updating')` test still passes
- **Action:** Run test, confirm conversions generated on update as well (no code changes, just verification)

---

## Phase 5: Update Gallery Upload Feature Tests

**Goal:** Extend existing Gallery controller tests to verify three conversions generated for each uploaded image in batch.

**Verification:** All Gallery controller tests pass including new conversion checks for multi-image uploads.

### Tasks

#### Task 5.1: Add test for conversions on all batch images
- **File:** `tests/Feature/Http/Controllers/Api/Gallery/StoreGalleryControllerTest.php`
- **Test:** `it('generates conversions for all uploaded gallery images')`
- **Setup:**
  - Fake storage
  - POST to `/api/galleries` with array of 3 images (1920×1080, 1600×900, 2400×1800)
- **Assertions:**
  - Gallery created successfully
  - `$gallery->getMedia('gallery_images')` has count 3
  - For each media item: all three conversions present (`hasGeneratedConversion()`)

#### Task 5.2: Add test for MediaResource structure in gallery response
- **File:** `tests/Feature/Http/Controllers/Api/Gallery/StoreGalleryControllerTest.php`
- **Test:** `it('returns all conversion URLs for each gallery image')`
- **Setup:**
  - Fake storage
  - POST to `/api/galleries` with 2 images
- **Assertions:**
  - Response status 201
  - `gallery.media` array has count 2
  - Each media item has `urls` object with four keys
  - URLs contain conversion names

#### Task 5.3: Update existing gallery image upload test
- **File:** `tests/Feature/Http/Controllers/Api/Gallery/StoreGalleryControllerTest.php`
- **Test:** Update `it('creates gallery with multiple images')` test (if exists)
- **Changes:** Add JSON structure assertion for new `urls` format

#### Task 5.4: Verify UpdateGalleryController conversions
- **File:** `tests/Feature/Http/Controllers/Api/Gallery/UpdateGalleryControllerTest.php`
- **Test:** Verify existing `it('appends images when updating')` test still passes
- **Action:** Run test, confirm conversions generated on update (no code changes)

---

## Phase 6: Run Full Test Suite and Verify

**Goal:** Ensure all 132+ tests pass including new tests, no regressions introduced.

**Verification:** `php artisan test` completes with 0 failures.

### Tasks

#### Task 6.1: Run full test suite
- **Command:** `php artisan test`
- **Expected:** All tests pass (should be ~140 tests now with new additions)
- **Action:** If failures, debug and fix before proceeding

#### Task 6.2: Run specific test groups
- **Commands:**
  ```bash
  php artisan test --filter=MediaResource
  php artisan test --filter=ProductController
  php artisan test --filter=GalleryController
  ```
- **Purpose:** Verify each component group passes independently

#### Task 6.3: Verify no existing tests broken
- **Action:** Compare test count before/after implementation
- **Before:** 132 tests
- **After:** ~140 tests (8 new tests added)
- **Check:** No existing tests changed from passing to failing

---

## Phase 7: Update Documentation

**Goal:** Document new conversion patterns, deployment requirements, and regeneration process.

**Verification:** Documentation complete and clear for future deployment.

### Tasks

#### Task 7.1: Update CLAUDE.md medialibrary-development skill
- **File:** `CLAUDE.md`
- **Action:** Update Skills section with new conversion pattern example
- **Add to medialibrary-development description:**
  ```
  Current patterns:
  - Three responsive conversions: thumb (400×400 crop), medium (1200px), large (2000px)
  - Quality: 80-85 WebP with optimize()
  - MediaResource returns nested urls object: {thumb, medium, large, original}
  ```

#### Task 7.2: Document deployment checklist
- **File:** `CLAUDE.md` or new `docs/deployment.md`
- **Content:**
  ```
  Image Optimization Deployment Checklist:
  1. Verify cwebp binary installed: `which cwebp` returns path
  2. Install if missing: `apt-get install webp` (Debian/Ubuntu)
  3. Deploy backend code
  4. Test upload: verify 3 conversions generated
  5. Regenerate old media: `php artisan media-library:regenerate`
  6. Monitor logs for optimizer warnings
  7. Verify storage size decreased ~10% per image
  8. Update frontend to consume urls.medium/large/thumb
  ```

#### Task 7.3: Document regeneration command usage
- **File:** `CLAUDE.md` Commands section
- **Add:**
  ```bash
  # Regenerate media conversions for existing uploads
  php artisan media-library:regenerate

  # Scope to specific model
  php artisan media-library:regenerate --model=App\\Models\\Product

  # Scope to specific collection
  php artisan media-library:regenerate --collection=product_images
  ```

#### Task 7.4: Update auto memory
- **File:** `C:\Users\Lecoc\.claude\projects\c--laragon-www-projet-dev-projet-perso-pensee-boheme-back\memory\MEMORY.md`
- **Add to Testing Patterns section:**
  ```
  - MediaResource tests use `getUrlSafely()` helper for backwards compatibility
  - Conversion tests check `hasGeneratedConversion()` for all three sizes (thumb, medium, large)
  - Storage::fake('media') required for media upload tests
  ```

---

## Phase 8: Manual Deployment Verification

**Goal:** Verify feature works end-to-end on development server with real images.

**Verification:** Upload real image, see three conversions in storage, API returns correct structure.

### Tasks

#### Task 8.1: Test Product upload with real image
- **Tool:** Postman or curl
- **Action:**
  ```bash
  curl -X POST http://localhost/api/products \
    -H "Authorization: Bearer {token}" \
    -F "name=Test Product" \
    -F "slug=test-product-conversions" \
    -F "category_id=1" \
    -F "image=@/path/to/real-photo.jpg"
  ```
- **Verification:**
  - 201 response
  - Response JSON has `product.media[0].urls` with 4 URLs
  - Check `storage/app/public/media-library/`: see 3 WebP files in conversions/

#### Task 8.2: Test Gallery upload with multiple images
- **Tool:** Postman or curl
- **Action:**
  ```bash
  curl -X POST http://localhost/api/galleries \
    -H "Authorization: Bearer {token}" \
    -F "name=Test Gallery" \
    -F "slug=test-gallery-multi" \
    -F "images[]=@/path/to/photo1.jpg" \
    -F "images[]=@/path/to/photo2.jpg"
  ```
- **Verification:**
  - 201 response
  - Each image in response has `urls` object
  - Storage shows 3 conversions per image (6 total conversion files)

#### Task 8.3: Verify optimizer ran
- **Action:** Check Laravel logs
- **Search for:** "cwebp" or "image-optimizer" messages
- **Expected:** No "optimizer not found" warnings
- **If warnings present:** Install webp package on server

#### Task 8.4: Test backwards compatibility
- **Action:**
  - Find old media in database (uploaded before this change)
  - Fetch via API (GET `/api/products/{id}` or `/api/galleries/{id}`)
- **Verification:**
  - Response has `urls` object
  - All URLs point to original (no 404 errors)
  - Frontend can still display images (using original URL)

#### Task 8.5: Run regeneration command
- **Command:** `php artisan media-library:regenerate --model=App\\Models\\Product`
- **Expected:**
  - Progress output showing media items processed
  - After completion, old media now have three conversions
  - Re-fetch via API: `urls` object now has distinct thumb/medium/large URLs

---

## Summary

**Total Phases:** 8
**Total Tasks:** 35
**Test Files Created:** 1 (MediaResourceTest.php)
**Test Files Modified:** 4 (StoreProductControllerTest, UpdateProductControllerTest, StoreGalleryControllerTest, UpdateGalleryControllerTest)
**Code Files Modified:** 3 (Product.php, Gallery.php, MediaResource.php)
**Documentation Files Modified:** 2 (CLAUDE.md, MEMORY.md)

**Estimated Test Count After Implementation:** ~140 tests (up from 132)

**Breaking Changes:**
- MediaResource response structure changes from flat `url` to nested `urls` object
- Frontend must be updated after backend deployment to consume new structure
- Coordinate deployment: backend first, regenerate old media, then frontend update
