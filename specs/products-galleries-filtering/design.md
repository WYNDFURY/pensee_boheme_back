# Design — Products & Galleries Visibility Filtering

## Overview

Add auth-aware visibility filtering to the Product and Gallery public endpoints. Unauthenticated requests see only active/published records; authenticated (admin) requests see all. Also: remove unused `IndexPageController`, order galleries by `order` column, and auto-increment `order` on gallery creation.

The approach uses Eloquent local scopes for reusable query filtering, optional Sanctum auth detection in controllers (no middleware change on public routes), and query-level filtering in show controllers via explicit lookups replacing implicit route model binding.

## Architecture

```
Request → Route (no middleware change) → Controller
                                            │
                                    auth('sanctum')->check()
                                       /              \
                                  false                true
                                    │                    │
                          Model::query()           Model::query()
                          ->scopeActive()          (no scope)
                          ->where(...)             ->where(...)
                                    \              /
                                     Resource → JSON
```

Key principle: public GET routes remain middleware-free. Auth detection is optional — `auth('sanctum')->check()` reads the Bearer token if present but does not reject requests without one.

## Components and Interfaces

### 1. Model Scopes (new)

**Product model** — add `scopeActive`:

```php
// app/Models/Product.php
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}
```

**Gallery model** — add `scopePublished`:

```php
// app/Models/Gallery.php
public function scopePublished(Builder $query): Builder
{
    return $query->where('is_published', true);
}
```

Both require `use Illuminate\Database\Eloquent\Builder;` import.

### 2. IndexProductController (modify) — REQ-1

Current: `Product::with('category')->get()`

```php
public function __invoke()
{
    $query = Product::with('category');

    if (! auth('sanctum')->check()) {
        $query->active();
    }

    return ProductResource::collection($query->get());
}
```

### 3. ShowProductController (modify) — REQ-2

Current: uses implicit route model binding `Product $product`.

Problem: route model binding resolves any product by ID regardless of `is_active`. To return 404 for inactive products when unauthenticated, we must replace implicit binding with an explicit query.

```php
public function __invoke(int $product)
{
    $query = Product::query();

    if (! auth('sanctum')->check()) {
        $query->active();
    }

    $product = $query->findOrFail($product);

    $product->load([
        'media' => fn ($q) => $q->where('collection_name', 'product_images'),
        'options',
        'category' => fn ($q) => $q->orderBy('order', 'asc'),
    ]);

    return new ProductResource($product);
}
```

Route stays `Route::get('/{product}', ...)` — Laravel passes the raw `{product}` value as the `int $product` parameter when no type-hinted model is present.

### 4. IndexGalleryController (modify) — REQ-3, REQ-7

Current: fetches all galleries, sorts in-memory by id desc, filters galleries without media.

Replace with query-level filtering and ordering:

```php
public function __invoke()
{
    $query = Gallery::with('media')->orderBy('order', 'asc');

    if (! auth('sanctum')->check()) {
        $query->published();
    }

    $galleries = $query->get();

    // Keep existing filter: exclude galleries with no media (public only)
    if (! auth('sanctum')->check()) {
        $galleries = $galleries->filter(fn ($gallery) =>
            $gallery->getMedia('gallery_images')->isNotEmpty()
        )->values();
    }

    return GalleryResource::collection($galleries);
}
```

Note: the "has media" filter remains in-memory because media is loaded as a relation via Spatie MediaLibrary (polymorphic), and checking `gallery_images` collection emptiness at query level would require a complex subquery. The `is_published` and `order` filters happen at query level. For authenticated admin requests, all galleries (even empty ones) are returned for management.

### 5. ShowGalleryController (modify) — REQ-4

Current: uses implicit route model binding `Gallery $gallery` resolved by slug.

Replace with explicit query:

```php
public function __invoke(string $gallery)
{
    $query = Gallery::where('slug', $gallery);

    if (! auth('sanctum')->check()) {
        $query->published();
    }

    $gallery = $query->firstOrFail();

    $gallery->load('media');

    return new GalleryResource($gallery);
}
```

Route stays `Route::get('/{gallery:slug}', ...)` — change to `Route::get('/{gallery}', ...)` since we now handle slug lookup manually. Or keep `{gallery:slug}` and accept a `string $gallery` parameter — Laravel passes the raw segment value when there's no type-hinted model.

Decision: change route to `/{gallery}` (plain parameter) for clarity since we no longer rely on route model binding. Update the protected routes (`patch`, `delete`) to also use `/{gallery}` and resolve by slug manually in their controllers — but this is out of scope. Simpler: keep the route as `/{gallery:slug}` but rename the controller parameter to `string $slug` for clarity:

```php
public function __invoke(string $slug)
{
    $query = Gallery::where('slug', $slug);

    if (! auth('sanctum')->check()) {
        $query->published();
    }

    $gallery = $query->firstOrFail();
    $gallery->load('media');

    return new GalleryResource($gallery);
}
```

Route change: `Route::get('/{gallery:slug}', ...)` → `Route::get('/{slug}', ...)`. The `auth:sanctum` protected routes (`patch`, `delete`) keep using `{gallery:slug}` with implicit binding since they always require auth and should access any gallery.

Final routes/api/galleries.php:

```php
Route::prefix('galleries')->name('galleries.')->group(function () {
    Route::get('/', IndexGalleryController::class)->name('index');
    Route::get('/{slug}', ShowGalleryController::class)->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', StoreGalleryController::class)->name('store');
        Route::patch('/{gallery:slug}', UpdateGalleryController::class)->name('update');
        Route::delete('/{gallery:slug}', DestroyGalleryController::class)->name('destroy');
    });
});
```

### 6. ShowPageController (modify) — REQ-5

Current: hard-codes `->where('is_active', true)` on `categories.products` eager load.

Make conditional:

```php
public function __invoke(Page $page)
{
    $isAuthenticated = auth('sanctum')->check();

    $page->load([
        'categories' => fn ($query) => $query->orderBy('order', 'asc'),
        'categories.products' => function ($query) use ($isAuthenticated) {
            if (! $isAuthenticated) {
                $query->where('is_active', true);
            }
            $query->orderBy('name', 'asc');
        },
        'categories.products.media' => fn ($query) =>
            $query->where('collection_name', 'product_images'),
    ]);

    return new PageResource($page);
}
```

### 7. Remove IndexPageController — REQ-6

Files to delete:
- `app/Http/Controllers/Page/IndexPageController.php`

Files to modify:
- `routes/api/pages.php` — remove `Route::get('/', IndexPageController::class)->name('index');` and the `use` import
- `tests/Feature/Http/Controllers/Api/Page/IndexPageControllerTest.php` — delete entire file

### 8. StoreGalleryController (modify) — REQ-8

Add auto-increment `order` when not provided:

```php
public function __invoke(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:galleries',
        'description' => 'nullable|string',
        'is_published' => 'sometimes|boolean',
        'order' => 'sometimes|integer',
        'images' => 'nullable|array|max:20',
        'images.*' => 'image|mimes:jpeg,png,webp,gif|max:10240',
    ]);

    $data = collect($validated)->except('images')->toArray();

    if (! array_key_exists('order', $data)) {
        $data['order'] = Gallery::max('order') + 1;
    }

    $gallery = Gallery::create($data);

    // ... rest unchanged
}
```

`Gallery::max('order')` returns `null` when table is empty; `null + 1 = 1` in PHP (with a deprecation notice in 8.1+). Use explicit fallback:

```php
$data['order'] = (Gallery::max('order') ?? -1) + 1;
```

This yields `0` for empty table, `1` when max is `0`, etc. Since existing galleries all have `order = 0`, the first new gallery gets `order = 1`.

## Data Models

No schema changes. Existing columns used:

| Model | Column | Type | Default | Purpose |
|---|---|---|---|---|
| Product | `is_active` | boolean | `true` | Visibility flag |
| Gallery | `is_published` | boolean | `false` | Visibility flag |
| Gallery | `order` | integer | `0` | Sort position |

## Error Handling

- **404 on inactive/unpublished show routes**: `firstOrFail()` / `findOrFail()` throws `ModelNotFoundException`, which Laravel converts to a 404 JSON response automatically (since `Accept: application/json` is standard for API consumers).
- **No new error types introduced**. Existing validation and auth error handling unchanged.

## Testing Strategy

### Existing tests to modify

**IndexProductControllerTest** — currently creates products with factory (random `is_active`). Must be updated to explicitly test both auth states:

```php
it('returns only active products for unauthenticated users', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    get('/api/products')->assertOk()->assertJsonCount(1);
});

it('returns all products for authenticated users', function () {
    $this->actingAs(User::factory()->create());

    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    get('/api/products')->assertOk()->assertJsonCount(2);
});
```

**ShowProductControllerTest** — add inactive product 404 test:

```php
it('returns 404 for inactive product when unauthenticated', function () {
    $product = Product::factory()->create(['is_active' => false]);

    get("/api/products/{$product->id}")->assertNotFound();
});

it('returns inactive product for authenticated users', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create(['is_active' => false]);

    get("/api/products/{$product->id}")->assertOk();
});
```

**IndexGalleryControllerTest** — test `is_published` filtering and `order` sorting:

```php
it('returns only published galleries with media for unauthenticated users', function () {
    Storage::fake('media');

    $published = Gallery::factory()->create(['is_published' => true]);
    $published->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('gallery_images');

    $unpublished = Gallery::factory()->create(['is_published' => false]);
    $unpublished->addMedia(UploadedFile::fake()->image('photo2.jpg'))
        ->toMediaCollection('gallery_images');

    get('/api/galleries')->assertOk()->assertJsonCount(1);
});

it('returns all galleries for authenticated users', function () {
    $this->actingAs(User::factory()->create());

    Gallery::factory()->count(3)->create();

    get('/api/galleries')->assertOk()->assertJsonCount(3);
});

it('returns galleries ordered by order column', function () {
    Storage::fake('media');

    $second = Gallery::factory()->create(['is_published' => true, 'order' => 2]);
    $first = Gallery::factory()->create(['is_published' => true, 'order' => 1]);

    // Add media to both
    $second->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection('gallery_images');
    $first->addMedia(UploadedFile::fake()->image('b.jpg'))->toMediaCollection('gallery_images');

    $response = get('/api/galleries')->assertOk();
    expect($response->json('0.order'))->toBe(1);
    expect($response->json('1.order'))->toBe(2);
});
```

**ShowGalleryControllerTest** — add unpublished 404 test:

```php
it('returns 404 for unpublished gallery when unauthenticated', function () {
    $gallery = Gallery::factory()->create(['is_published' => false]);

    get("/api/galleries/{$gallery->slug}")->assertNotFound();
});

it('returns unpublished gallery for authenticated users', function () {
    $this->actingAs(User::factory()->create());
    $gallery = Gallery::factory()->create(['is_published' => false]);

    get("/api/galleries/{$gallery->slug}")->assertOk();
});
```

**ShowPageControllerTest** — existing test `'returns page with nested categories and active products'` asserts 1 product filtered from 2. Add authenticated variant:

```php
it('returns page with all products including inactive for authenticated users', function () {
    $this->actingAs(User::factory()->create());

    $page = Page::factory()->create();
    $category = Category::factory()->create(['page_id' => $page->id, 'order' => 1]);
    Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
    Product::factory()->create(['category_id' => $category->id, 'is_active' => false]);

    get("/api/pages/{$page->slug}")
        ->assertOk()
        ->assertJsonCount(1, 'categories')
        ->assertJsonCount(2, 'categories.0.products');
});
```

**StoreGalleryControllerTest** — add auto-increment order test:

```php
it('auto-increments order when not provided', function () {
    Gallery::factory()->create(['order' => 5]);

    postJson('/api/galleries', [
        'name' => 'New Gallery',
        'slug' => 'new-gallery',
    ])->assertCreated()->assertJsonPath('data.order', 6);
});

it('uses provided order value when given', function () {
    postJson('/api/galleries', [
        'name' => 'Custom Order',
        'slug' => 'custom-order',
        'order' => 42,
    ])->assertCreated()->assertJsonPath('data.order', 42);
});
```

**IndexPageControllerTest** — delete entire file (REQ-6).

### Unit tests for scopes

```php
// tests/Unit/Models/ProductTest.php
it('has active scope that filters by is_active', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    expect(Product::active()->count())->toBe(1);
});

// tests/Unit/Models/GalleryTest.php
it('has published scope that filters by is_published', function () {
    Gallery::factory()->create(['is_published' => true]);
    Gallery::factory()->create(['is_published' => false]);

    expect(Gallery::published()->count())->toBe(1);
});
```

## Performance Considerations

- All visibility filtering uses SQL `WHERE` clauses — no change in query count
- Gallery index removes in-memory `sortBy('id')` and replaces with `ORDER BY order` at query level — same or better performance
- The "has media" in-memory filter on gallery index remains (polymorphic relation makes SQL filtering complex). This is acceptable given the small gallery count (~20-50 expected)
- `Gallery::max('order')` on store is a single `SELECT MAX(order) FROM galleries` — negligible cost

## Security Considerations

- Public routes remain unauthenticated — `auth('sanctum')->check()` is read-only, never rejects
- `findOrFail()` / `firstOrFail()` ensures inactive/unpublished records are truly invisible (404), not just hidden from lists
- No new attack surface — existing Sanctum token validation unchanged
- Admin-only store/update/delete routes still require `auth:sanctum` middleware

## Monitoring and Observability

No new monitoring needed. Existing Laravel logging covers:
- 404 responses for inactive/unpublished show requests (standard `ModelNotFoundException`)
- Auth detection via Sanctum is transparent and stateless

If needed later, a custom query log scope could track how often inactive items are requested, but this is not required for this feature.
