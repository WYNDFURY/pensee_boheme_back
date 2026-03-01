# Design — API Response Standardization

## Overview

Introduce `JsonResource::withoutWrapping()` globally to eliminate the automatic `data` envelope on resource responses. Standardize every controller to return through a Resource class. Unify mutation responses to `{ "message": "...", "data": {...} }`.

Single rule after this change:
- **GET list** → `[{...}, ...]` (flat array)
- **GET single** → `{...}` (flat object)
- **POST / PATCH** → `{ "message": "...", "data": {...} }`
- **DELETE** → `{ "message": "..." }` (unchanged)

---

## Architecture

```
AppServiceProvider::boot()
  └── JsonResource::withoutWrapping()   ← removes "data" envelope globally

Controllers (every read endpoint)
  └── return ResourceClass / ResourceClass::collection()   ← no response()->json() wrapper

Controllers (every mutation)
  └── response()->json(['message' => '...', 'data' => new Resource($model)], $status)
```

---

## Components and Interfaces

### 1. AppServiceProvider

**File:** `app/Providers/AppServiceProvider.php`

Add to `boot()`:
```php
use Illuminate\Http\Resources\Json\JsonResource;

JsonResource::withoutWrapping();
```

---

### 2. New Resource — UserResource

**File:** `app/Http/Resources/UserResource.php`

```php
return [
    'id'         => $this->id,
    'first_name' => $this->first_name,
    'last_name'  => $this->last_name,
    'email'      => $this->email,
];
```

No `password`, no `remember_token`. Mirrors the fields already returned by the raw model in existing controllers.

---

### 3. Controller Changes

#### Product

| Controller | Before | After |
|---|---|---|
| `IndexProductController` | `response()->json(ProductResource::collection(...))` | `return ProductResource::collection(Product::with('category')->get())` |
| `StoreProductController` | `'product' => new ProductResource(...)` | `'data' => new ProductResource(...)` |
| `UpdateProductController` | `'product' => new ProductResource(...)` | `'data' => new ProductResource(...)` |
| `ShowProductController` | `return new ProductResource(...)` ✓ | no change |
| `DestroyProductController` | `{ "message": "..." }` ✓ | no change |

#### Category

| Controller | Before | After |
|---|---|---|
| `IndexCategoryController` | `response()->json(Category::all())` | `return CategoryResource::collection(Category::all())` |
| `ShowCategoryController` | `response()->json($category)` | `$category->load('page'); return new CategoryResource($category)` |
| `StoreCategoryController` | `'category' => new CategoryResource(...)` | `'data' => new CategoryResource(...)` |
| `UpdateCategoryController` | `'category' => $category` (raw) | `$category->load('page'); 'data' => new CategoryResource($category)` |
| `DestroyCategoryController` | `{ "message": "..." }` ✓ | no change |

#### Gallery

| Controller | Before | After |
|---|---|---|
| `IndexGalleryController` | `return GalleryResource::collection(...)` ✓ | no change |
| `ShowGalleryController` | `return new GalleryResource(...)` ✓ | no change |
| `StoreGalleryController` | `'gallery' => new GalleryResource(...)` | `'data' => new GalleryResource(...)` |
| `UpdateGalleryController` | `'gallery' => new GalleryResource(...)` | `'data' => new GalleryResource(...)` |
| `DestroyGalleryController` | `{ "message": "..." }` ✓ | no change |

#### Page

| Controller | Before | After |
|---|---|---|
| `IndexPageController` | `response()->json(Page::all())` | `return PageResource::collection(Page::all())` |
| `ShowPageController` | `return new PageResource(...)` ✓ | no change |
| `StorePageController` | `'page' => $page` (raw) | `'data' => new PageResource($page)` |
| `UpdatePageController` | `'page' => $page` (raw) | `'data' => new PageResource($page)` |
| `DestroyPageController` | `{ "message": "..." }` ✓ | no change |

#### User

| Controller | Before | After |
|---|---|---|
| `IndexUserController` | `response()->json(User::all())` | `return UserResource::collection(User::all())` |
| `ShowUserController` | `response()->json($user)` | `return new UserResource($user)` |
| `StoreUserController` | `response()->json($user, 201)` | `response()->json(['message' => 'User created successfully', 'data' => new UserResource($user)], 201)` |
| `UpdateUserController` | `response()->json($user)` | `response()->json(['message' => 'User updated successfully', 'data' => new UserResource($user)])` |
| `DestroyUserController` | `{ "message": "..." }` ✓ | no change |

---

### 4. Test Changes

Tests asserting on response paths must be updated. Validation tests, `assertDatabaseHas`, and `assertCreated`/`assertOk`/`assertNotFound` are unaffected.

#### ShowProductControllerTest
```php
// Before
->assertJsonPath('data.id', $product->id)
->assertJsonPath('data.name', $product->name)
->assertJsonPath('data.category_name', ...)
// After
->assertJsonPath('id', $product->id)
->assertJsonPath('name', $product->name)
->assertJsonPath('category_name', ...)
```

#### StoreProductControllerTest
```php
// Before
->assertJsonPath('product.name', ...)
->assertJsonPath('product.slug', ...)
->assertJsonCount(1, 'product.media')
->assertJsonStructure(['product' => ['media' => [['urls' => [...]]]]])
->assertJsonCount(0, 'product.media')
// After
->assertJsonPath('data.name', ...)
->assertJsonPath('data.slug', ...)
->assertJsonCount(1, 'data.media')
->assertJsonStructure(['data' => ['media' => [['urls' => [...]]]]])
->assertJsonCount(0, 'data.media')
```

#### UpdateProductControllerTest
```php
// Before
->assertJsonPath('product.name', ...)
->assertJsonCount(1, 'product.media')
// After
->assertJsonPath('data.name', ...)
->assertJsonCount(1, 'data.media')
```

#### StoreCategoryControllerTest
```php
// Before
->assertJsonPath('category.name', ...)
->assertJsonPath('category.page_slug', ...)
// After
->assertJsonPath('data.name', ...)
->assertJsonPath('data.page_slug', ...)
```

#### UpdateCategoryControllerTest
```php
// Before
->assertJsonPath('category.name', ...)
// After
->assertJsonPath('data.name', ...)
```

#### IndexGalleryControllerTest
```php
// Before
->assertJsonCount(0, 'data')
->assertJsonCount(1, 'data')
->assertJsonPath('data.0.images_count', 5)
->assertJsonCount(3, 'data.0.media')
// After
->assertJsonCount(0)
->assertJsonCount(1)
->assertJsonPath('0.images_count', 5)
->assertJsonCount(3, '0.media')
```

#### ShowGalleryControllerTest
```php
// Before
->assertJsonPath('data.slug', ...)
->assertJsonPath('data.name', ...)
// After
->assertJsonPath('slug', ...)
->assertJsonPath('name', ...)
```

#### StoreGalleryControllerTest
```php
// Before
->assertJsonPath('gallery.name', ...)
->assertJsonPath('gallery.slug', ...)
->assertJsonCount(3, 'gallery.media')
->assertJsonStructure(['gallery' => ['media' => [['urls' => [...]]]]])
->assertJsonCount(0, 'gallery.media')
// After
->assertJsonPath('data.name', ...)
->assertJsonPath('data.slug', ...)
->assertJsonCount(3, 'data.media')
->assertJsonStructure(['data' => ['media' => [['urls' => [...]]]]])
->assertJsonCount(0, 'data.media')
```

#### UpdateGalleryControllerTest
```php
// Before
->assertJsonPath('gallery.name', ...)
->assertJsonCount(3, 'gallery.media')
->assertJsonCount(1, 'gallery.media')
// After
->assertJsonPath('data.name', ...)
->assertJsonCount(3, 'data.media')
->assertJsonCount(1, 'data.media')
```

#### ShowPageControllerTest
```php
// Before
->assertJsonPath('data.slug', ...)
->assertJsonCount(1, 'data.categories')
->assertJsonCount(1, 'data.categories.0.products')
// After
->assertJsonPath('slug', ...)
->assertJsonCount(1, 'categories')
->assertJsonCount(1, 'categories.0.products')
```

#### StorePageControllerTest
```php
// Before
->assertJsonPath('page.slug', ...)
// After
->assertJsonPath('data.slug', ...)
```

#### UpdatePageControllerTest
```php
// Before
->assertJsonPath('page.slug', ...)
// After
->assertJsonPath('data.slug', ...)
```

#### StoreUserControllerTest
```php
// Before
->assertJsonPath('first_name', ...)
->assertJsonPath('last_name', ...)
->assertJsonPath('email', ...)
// After
->assertJsonPath('data.first_name', ...)
->assertJsonPath('data.last_name', ...)
->assertJsonPath('data.email', ...)
```

#### UpdateUserControllerTest
```php
// Before
->assertJsonPath('first_name', ...)
// After
->assertJsonPath('data.first_name', ...)
```

#### Unaffected tests (no path changes needed)
- `IndexProductControllerTest` — `assertJsonCount(3)` on flat array ✓
- `IndexCategoryControllerTest` — `assertJsonCount(3)` on flat array ✓
- `ShowCategoryControllerTest` — `assertJsonPath('id', ...)`, `assertJsonPath('name', ...)` — CategoryResource has these ✓
- `IndexPageControllerTest` — `assertJsonCount(3)` on flat array ✓
- `IndexUserControllerTest` — `assertJsonCount(3)` on flat array ✓
- `ShowUserControllerTest` — `assertJsonPath('id', ...)`, `assertJsonPath('first_name', ...)` — UserResource has these ✓
- All validation tests, `assertDatabaseHas`, delete tests ✓

---

### 5. API Reference Update

**File:** `docs/api-reference.md`

Update the `## Response Wrapping` table:

```
| Endpoint            | Before              | After         |
|---------------------|---------------------|---------------|
| GET /galleries      | { "data": [...] }   | [...]         |
| GET /instagram      | { "data": [...] }   | [...]         |
| GET /products/{id}  | { "data": {...} }   | {...}         |
| GET /galleries/{id} | { "data": {...} }   | {...}         |
| GET /pages/{slug}   | { "data": {...} }   | {...}         |
| POST/PATCH          | { "message", "X" }  | { "message", "data" } |
```

---

## Error Handling

No changes. Laravel's validation (422), auth (401), and not-found (404) error responses are not produced by Resource classes and are unaffected by `withoutWrapping()`.

---

## Testing Strategy

1. Run `php artisan test` — must pass 143 tests before starting
2. Add `withoutWrapping()` to AppServiceProvider — run tests, identify failures
3. Apply controller and test changes group by group (Product → Category → Gallery → Page → User)
4. Run `php artisan test` — all 143 tests must pass at the end

---

## Performance Considerations

- `ShowCategoryController` and `UpdateCategoryController` now call `$category->load('page')` — adds one query per request. Acceptable given this is an admin-only endpoint.
- `IndexCategoryController` now uses `CategoryResource::collection()` which accesses `$this->page` per item via `whenLoaded` — since `page` is not eagerly loaded in the index query, `page_slug` will be `null` for all items in the index (page relation not loaded = null, per `$this->page ? $this->page->slug : null`). This is correct behavior: the index returns lightweight category data; the `page` relation is only loaded on show/store/update.

## Security Considerations

- `UserResource` must not expose `password` or `remember_token`. Only expose `id`, `first_name`, `last_name`, `email`.
- Raw model serialization (current behavior on User endpoints) already hides `password` via `$hidden` on the User model, but explicit Resource class makes this contract explicit and controlled.
