# Tasks — API Response Standardization

## Phase 1 — Global unwrapping + Product

**Goal:** Remove the `data` envelope globally and fix the Product group (most used resource). All product tests green.

**Verify:** `php artisan test --filter=Product` passes.

---

### Task 1.1 — Add `JsonResource::withoutWrapping()` to AppServiceProvider

**File:** `app/Providers/AppServiceProvider.php`

```php
use Illuminate\Http\Resources\Json\JsonResource;

public function boot(): void
{
    Schema::defaultStringLength(191);
    JsonResource::withoutWrapping();
}
```

---

### Task 1.2 — Fix IndexProductController

**File:** `app/Http/Controllers/Product/IndexProductController.php`

Remove `response()->json()` wrapper — return collection directly:
```php
return ProductResource::collection(Product::with('category')->get());
```

Test: `IndexProductControllerTest` — `assertJsonCount(3)` on flat array. No test change needed.

---

### Task 1.3 — Fix StoreProductController mutation key

**File:** `app/Http/Controllers/Product/StoreProductController.php`

```php
// Before
'product' => new ProductResource($product)
// After
'data' => new ProductResource($product)
```

---

### Task 1.4 — Fix UpdateProductController mutation key

**File:** `app/Http/Controllers/Product/UpdateProductController.php`

```php
// Before
'product' => new ProductResource($product)
// After
'data' => new ProductResource($product)
```

---

### Task 1.5 — Update Product tests

**File:** `tests/Feature/Http/Controllers/Api/Product/ShowProductControllerTest.php`
```php
// data.* → root
->assertJsonPath('id', $product->id)
->assertJsonPath('name', $product->name)
->assertJsonPath('category_name', $product->category->name)
```

**File:** `tests/Feature/Http/Controllers/Api/Product/StoreProductControllerTest.php`
```php
// product.* → data.*
->assertJsonPath('data.name', 'Peigne Fleur')
->assertJsonPath('data.slug', 'peigne-fleur')
->assertJsonCount(1, 'data.media')
->assertJsonStructure(['data' => ['media' => [['urls' => ['thumb', 'medium', 'large', 'original']]]]])
->assertJsonCount(0, 'data.media')
```

**File:** `tests/Feature/Http/Controllers/Api/Product/UpdateProductControllerTest.php`
```php
// product.* → data.*
->assertJsonPath('data.name', 'Updated Name')
->assertJsonPath('data.name', 'With Image')
->assertJsonCount(1, 'data.media')
->assertJsonCount(1, 'data.media')  // preserves existing
```

---

## Phase 2 — Category

**Goal:** All category endpoints go through `CategoryResource`. Category tests green.

**Verify:** `php artisan test --filter=Category` passes.

---

### Task 2.1 — Fix IndexCategoryController

**File:** `app/Http/Controllers/Category/IndexCategoryController.php`

```php
use App\Http\Resources\CategoryResource;

return CategoryResource::collection(Category::all());
```

Test: `IndexCategoryControllerTest` — `assertJsonCount(3)`. No test change needed.

---

### Task 2.2 — Fix ShowCategoryController

**File:** `app/Http/Controllers/Category/ShowCategoryController.php`

```php
use App\Http\Resources\CategoryResource;

public function __invoke(Category $category)
{
    $category->load('page');
    return new CategoryResource($category);
}
```

Test: `ShowCategoryControllerTest` — `assertJsonPath('id', ...)`, `assertJsonPath('name', ...)` still work since CategoryResource exposes these. No test change needed.

---

### Task 2.3 — Fix StoreCategoryController mutation key

**File:** `app/Http/Controllers/Category/StoreCategoryController.php`

```php
// Before
'category' => new CategoryResource($category)
// After
'data' => new CategoryResource($category)
```

---

### Task 2.4 — Fix UpdateCategoryController

**File:** `app/Http/Controllers/Category/UpdateCategoryController.php`

```php
use App\Http\Resources\CategoryResource;

$category->update($validated);
$category->load('page');

return response()->json([
    'message' => 'Category updated successfully',
    'data' => new CategoryResource($category),
]);
```

Remove unused `use Illuminate\Validation\ValidationException;` import.

---

### Task 2.5 — Update Category tests

**File:** `tests/Feature/Http/Controllers/Api/Category/StoreCategoryControllerTest.php`
```php
// category.* → data.*
->assertJsonPath('data.name', 'Accessoires')
->assertJsonPath('data.page_slug', $page->slug)
```

**File:** `tests/Feature/Http/Controllers/Api/Category/UpdateCategoryControllerTest.php`
```php
// category.* → data.*
->assertJsonPath('data.name', 'Updated')
```

---

## Phase 3 — Gallery

**Goal:** Gallery mutation responses use `data` key. Gallery tests green.

**Verify:** `php artisan test --filter=Gallery` passes.

---

### Task 3.1 — Fix StoreGalleryController mutation key

**File:** `app/Http/Controllers/Gallery/StoreGalleryController.php`

```php
// Before
'gallery' => new GalleryResource($gallery)
// After
'data' => new GalleryResource($gallery)
```

---

### Task 3.2 — Fix UpdateGalleryController mutation key

**File:** `app/Http/Controllers/Gallery/UpdateGalleryController.php`

```php
// Before
'gallery' => new GalleryResource($gallery)
// After
'data' => new GalleryResource($gallery)
```

---

### Task 3.3 — Update Gallery tests

**File:** `tests/Feature/Http/Controllers/Api/Gallery/IndexGalleryControllerTest.php`
```php
// data → root
->assertJsonCount(0)           // was: assertJsonCount(0, 'data')
->assertJsonCount(1)           // was: assertJsonCount(1, 'data')
->assertJsonPath('0.images_count', 5)   // was: 'data.0.images_count'
->assertJsonCount(3, '0.media')         // was: 'data.0.media'
```

**File:** `tests/Feature/Http/Controllers/Api/Gallery/ShowGalleryControllerTest.php`
```php
// data.* → root
->assertJsonPath('slug', $gallery->slug)   // was: 'data.slug'
->assertJsonPath('name', $gallery->name)   // was: 'data.name'
```

**File:** `tests/Feature/Http/Controllers/Api/Gallery/StoreGalleryControllerTest.php`
```php
// gallery.* → data.*
->assertJsonPath('data.name', 'Bohème Chic')
->assertJsonPath('data.slug', 'boheme-chic')
->assertJsonPath('data.name', 'Wedding Gallery')
->assertJsonCount(3, 'data.media')
->assertJsonStructure(['data' => ['media' => [['urls' => ['thumb', 'medium', 'large', 'original']]]]])
->assertJsonCount(0, 'data.media')
```

**File:** `tests/Feature/Http/Controllers/Api/Gallery/UpdateGalleryControllerTest.php`
```php
// gallery.* → data.*
->assertJsonPath('data.name', 'Updated Name')
->assertJsonCount(3, 'data.media')
->assertJsonCount(1, 'data.media')
```

---

## Phase 4 — Page

**Goal:** All page endpoints go through `PageResource`. Page tests green.

**Verify:** `php artisan test --filter=Page` passes.

---

### Task 4.1 — Fix IndexPageController

**File:** `app/Http/Controllers/Page/IndexPageController.php`

```php
use App\Http\Resources\PageResource;

return PageResource::collection(Page::all());
```

`PageResource` uses `whenLoaded('categories')` — since `all()` doesn't load relations, index returns pages with just `id` and `slug`. Test: `assertJsonCount(3)`. No test change needed.

---

### Task 4.2 — Fix StorePageController

**File:** `app/Http/Controllers/Page/StorePageController.php`

```php
use App\Http\Resources\PageResource;

return response()->json([
    'message' => 'Page created successfully',
    'data' => new PageResource($page),
], 201);
```

---

### Task 4.3 — Fix UpdatePageController

**File:** `app/Http/Controllers/Page/UpdatePageController.php`

```php
use App\Http\Resources\PageResource;

return response()->json([
    'message' => 'Page updated successfully',
    'data' => new PageResource($page),
]);
```

---

### Task 4.4 — Update Page tests

**File:** `tests/Feature/Http/Controllers/Api/Page/ShowPageControllerTest.php`
```php
// data.* → root
->assertJsonPath('slug', $page->slug)          // was: 'data.slug'
->assertJsonCount(1, 'categories')             // was: 'data.categories'
->assertJsonCount(1, 'categories.0.products')  // was: 'data.categories.0.products'
```

**File:** `tests/Feature/Http/Controllers/Api/Page/StorePageControllerTest.php`
```php
// page.* → data.*
->assertJsonPath('data.slug', 'new-page')   // was: 'page.slug'
```

**File:** `tests/Feature/Http/Controllers/Api/Page/UpdatePageControllerTest.php`
```php
// page.* → data.*
->assertJsonPath('data.slug', 'new-slug')   // was: 'page.slug'
```

---

## Phase 5 — User

**Goal:** Create `UserResource`. All user endpoints go through it. User tests green.

**Verify:** `php artisan test --filter=User` passes.

---

### Task 5.1 — Create UserResource

**File:** `app/Http/Resources/UserResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
        ];
    }
}
```

---

### Task 5.2 — Fix IndexUserController

**File:** `app/Http/Controllers/User/IndexUserController.php`

```php
use App\Http\Resources\UserResource;

return UserResource::collection(User::all());
```

Test: `IndexUserControllerTest` — `assertJsonCount(3)`. No test change needed.

---

### Task 5.3 — Fix ShowUserController

**File:** `app/Http/Controllers/User/ShowUserController.php`

```php
use App\Http\Resources\UserResource;

return new UserResource($user);
```

Test: `ShowUserControllerTest` — `assertJsonPath('id', ...)`, `assertJsonPath('first_name', ...)` — UserResource exposes these. No test change needed.

---

### Task 5.4 — Fix StoreUserController

**File:** `app/Http/Controllers/User/StoreUserController.php`

```php
use App\Http\Resources\UserResource;

return response()->json([
    'message' => 'User created successfully',
    'data' => new UserResource($user),
], 201);
```

---

### Task 5.5 — Fix UpdateUserController

**File:** `app/Http/Controllers/User/UpdateUserController.php`

```php
use App\Http\Resources\UserResource;

return response()->json([
    'message' => 'User updated successfully',
    'data' => new UserResource($user),
]);
```

---

### Task 5.6 — Update User tests

**File:** `tests/Feature/Http/Controllers/Api/User/StoreUserControllerTest.php`
```php
// root → data.*
->assertJsonPath('data.first_name', 'Marie')
->assertJsonPath('data.last_name', 'Dupont')
->assertJsonPath('data.email', 'marie@example.com')
```

**File:** `tests/Feature/Http/Controllers/Api/User/UpdateUserControllerTest.php`
```php
// root → data.*
->assertJsonPath('data.first_name', 'Updated')
```

---

## Phase 6 — API Reference + Final validation

**Goal:** Documentation updated. Full test suite green.

**Verify:** `php artisan test` — all tests pass. Manual spot-check: `curl /api/products/1` returns flat object, `curl -X POST /api/products` returns `{message, data}`.

---

### Task 6.1 — Update docs/api-reference.md

**File:** `docs/api-reference.md`

1. Update `## Response Wrapping` table — replace the current table with:

```markdown
| Endpoint | Shape |
|---|---|
| GET (list) | `[...]` (flat array, no wrapper) |
| GET (single) | `{...}` (flat object, no wrapper) |
| POST / PATCH | `{ "message": "...", "data": {...} }` |
| DELETE | `{ "message": "..." }` |
```

2. Update response examples for each endpoint that previously showed `{ "data": ... }`:
   - `GET /products/{id}` — remove `data` wrapper from example
   - `GET /galleries/{slug}` — remove `data` wrapper
   - `GET /pages/{slug}` — remove `data` wrapper
   - `POST /products`, `POST /categories`, `POST /galleries`, `POST /pages`, `POST /users` — change named key to `data`
   - `PATCH` variants — same

---

### Task 6.2 — Full test run

```bash
php artisan test
```

Expected: all tests pass (143+). Zero failures.
