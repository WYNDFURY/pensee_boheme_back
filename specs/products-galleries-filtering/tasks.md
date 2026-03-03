# Implementation Plan — Products & Galleries Visibility Filtering

**Status: COMPLETED** — All phases implemented and verified. 155/155 tests passing.

## Phase 1: Model Scopes & Unit Tests [DONE]

**Goal:** Add reusable Eloquent scopes to Product and Gallery models, verified by unit tests.

### Task 1.1: Add `scopeActive` to Product model [DONE]

- Added `use Illuminate\Database\Eloquent\Builder;` import
- Added `scopeActive(Builder $query): Builder` method to `app/Models/Product.php`
- Unit test added to `tests/Unit/Models/ProductTest.php`

### Task 1.2: Add `scopePublished` to Gallery model [DONE]

- Added `use Illuminate\Database\Eloquent\Builder;` import
- Added `scopePublished(Builder $query): Builder` method to `app/Models/Gallery.php`
- Unit test added to `tests/Unit/Models/GalleryTest.php`

---

## Phase 2: Product Visibility Filtering (REQ-1, REQ-2) [DONE]

### Task 2.1: Update IndexProductController [DONE]

- Auth-aware query: `auth('sanctum')->check()` gates `->active()` scope

### Task 2.2: Update ShowProductController [DONE]

- Replaced implicit route model binding (`Product $product`) with explicit query (`int $product`)
- Uses `findOrFail()` with optional `->active()` scope for 404 on inactive products

### Task 2.3: Rewrite IndexProductControllerTest [DONE]

- 3 tests: active-only for guests, all for authed, empty when no active

### Task 2.4: Update ShowProductControllerTest [DONE]

- 4 tests: relationships, nonexistent 404, inactive 404 for guests, inactive accessible for authed

---

## Phase 3: Gallery Visibility Filtering & Ordering (REQ-3, REQ-4, REQ-7) [DONE]

### Task 3.1: Update IndexGalleryController [DONE]

- Query-level `orderBy('order', 'asc')` replaces in-memory `sortBy('id')`
- `published()` scope for unauthenticated, "has media" filter kept in-memory for public

### Task 3.2: Update ShowGalleryController [DONE]

- Replaced implicit route model binding with explicit `Gallery::where('slug', $slug)->firstOrFail()`
- `published()` scope gates unauthenticated access

### Task 3.3: Update gallery show route [DONE]

- Changed `/{gallery:slug}` to `/{slug}` for public show route
- Protected routes (`patch`, `delete`) unchanged

### Task 3.4: Rewrite IndexGalleryControllerTest [DONE]

- 6 tests: published+media filtering, empty media exclusion, all for authed, order sorting, images_count, empty state

### Task 3.5: Update ShowGalleryControllerTest [DONE]

- 4 tests: published by slug, nonexistent 404, unpublished 404 for guests, unpublished accessible for authed

---

## Phase 4: Page Controller Updates & Cleanup (REQ-5, REQ-6) [DONE]

### Task 4.1: Update ShowPageController [DONE]

- `is_active` filter on `categories.products` eager-load now conditional on `auth('sanctum')->check()`

### Task 4.2: Update ShowPageControllerTest [DONE]

- Added authenticated test: returns all products including inactive

### Task 4.3: Remove IndexPageController [DONE]

- Deleted `app/Http/Controllers/Page/IndexPageController.php`
- Removed route and import from `routes/api/pages.php`
- Deleted `tests/Feature/Http/Controllers/Api/Page/IndexPageControllerTest.php`

---

## Phase 5: Gallery Order Auto-Increment (REQ-8) [DONE]

### Task 5.1: Update StoreGalleryController [DONE]

- Auto-assigns `(Gallery::max('order') ?? -1) + 1` when `order` not in request
- Explicit `order` values used as-is

### Task 5.2: Add StoreGalleryController order tests [DONE]

- 3 tests: auto-increment, explicit value, empty table default

---

## Phase 6: Final Verification [DONE]

### Task 6.1: Full test suite [DONE]

155/155 tests passing (414 assertions). Also fixed pre-existing `LoginControllerTest` rate limit test (was sending 5 attempts against a `throttle:10,1` route — corrected to 10).

### Task 6.2: Code formatting [DONE]

`./vendor/bin/pint` — 59 style issues fixed across the codebase.

---

## Files Changed

| File | Action |
|---|---|
| `app/Models/Product.php` | Added `scopeActive` scope |
| `app/Models/Gallery.php` | Added `scopePublished` scope |
| `app/Http/Controllers/Product/IndexProductController.php` | Auth-aware filtering |
| `app/Http/Controllers/Product/ShowProductController.php` | Explicit query with `findOrFail` |
| `app/Http/Controllers/Gallery/IndexGalleryController.php` | Auth-aware filtering + `orderBy('order')` |
| `app/Http/Controllers/Gallery/ShowGalleryController.php` | Explicit slug query with `firstOrFail` |
| `app/Http/Controllers/Gallery/StoreGalleryController.php` | Auto-increment `order` |
| `app/Http/Controllers/Page/ShowPageController.php` | Conditional `is_active` filter |
| `routes/api/galleries.php` | Show route `/{gallery:slug}` → `/{slug}` |
| `routes/api/pages.php` | Removed index route |
| `app/Http/Controllers/Page/IndexPageController.php` | **Deleted** |
| `tests/Feature/.../Page/IndexPageControllerTest.php` | **Deleted** |
| `tests/Feature/.../Product/IndexProductControllerTest.php` | Rewritten |
| `tests/Feature/.../Product/ShowProductControllerTest.php` | Rewritten |
| `tests/Feature/.../Gallery/IndexGalleryControllerTest.php` | Rewritten |
| `tests/Feature/.../Gallery/ShowGalleryControllerTest.php` | Rewritten |
| `tests/Feature/.../Gallery/StoreGalleryControllerTest.php` | Added 3 order tests |
| `tests/Feature/.../Page/ShowPageControllerTest.php` | Added auth test |
| `tests/Feature/.../Auth/LoginControllerTest.php` | Fixed rate limit iteration count |
