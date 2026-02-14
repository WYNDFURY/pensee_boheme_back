# Admin Authentication & Back Office — Implementation Plan

## Phase 1: Middleware & Config

**Goal:** Authenticate middleware returns 401 JSON for all API requests. Admin seeder creates the admin user. `.env.example` has admin vars.

**Verify:** `php artisan migrate:fresh --seed` creates the admin user in the DB.

### Task 1.1 — Fix Authenticate middleware

Edit `app/Http/Middleware/Authenticate.php`. Change `redirectTo` to always return `null` — this is an API-only app, never redirect to a login route.

```php
protected function redirectTo(Request $request): ?string
{
    return null;
}
```

### Task 1.2 — Create AdminUserSeeder

Create `database/seeders/AdminUserSeeder.php`:
- Uses `User::firstOrCreate()` keyed on `email`
- Reads `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_FIRST_NAME`, `ADMIN_LAST_NAME` from env with sensible defaults
- Password hashed with `Hash::make()`

### Task 1.3 — Register AdminUserSeeder in DatabaseSeeder

Edit `database/seeders/DatabaseSeeder.php`. Add `AdminUserSeeder::class` to the `$this->call()` array.

### Task 1.4 — Update .env.example

Append:
```
ADMIN_EMAIL=admin@pensee-boheme.fr
ADMIN_PASSWORD=password
ADMIN_FIRST_NAME=Admin
ADMIN_LAST_NAME="Pensée Bohème"
```

---

## Phase 2: Auth Controllers & Routes

**Goal:** Login and logout endpoints work. Login returns a Sanctum token. Logout revokes it.

**Verify:** `php artisan test tests/Feature/Http/Controllers/Api/Auth/`

### Task 2.1 — Create LoginController

Create `app/Http/Controllers/Auth/LoginController.php`:
- Single-action invokable controller (`__invoke`)
- Validates `email` (required, string, email) and `password` (required, string)
- Looks up user by email, checks password with `Hash::check()`
- On failure: throws `ValidationException::withMessages(['email' => ['Invalid credentials.']])`
- On success: `$user->createToken('admin-token')->plainTextToken` → returns JSON with `token` and `user` object (`id`, `first_name`, `last_name`, `email`)

### Task 2.2 — Create LogoutController

Create `app/Http/Controllers/Auth/LogoutController.php`:
- Single-action invokable controller
- `$request->user()->currentAccessToken()->delete()`
- Returns `{'message': 'Logged out'}` with 200

### Task 2.3 — Create auth route file

Create `routes/api/auth.php`:
```php
Route::post('/login', LoginController::class)->middleware('throttle:5,1')->name('login');
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum')->name('logout');
```

### Task 2.4 — Register auth routes in api.php

Edit `routes/api.php`. Add `require __DIR__.'/api/auth.php';` inside the main route group.

### Task 2.5 — Write LoginControllerTest

Create `tests/Feature/Http/Controllers/Api/Auth/LoginControllerTest.php`:
- `it returns token with valid credentials` — create user with known password, postJson `/api/login` → assertOk, assertJsonStructure `['token', 'user' => ['id', 'first_name', 'last_name', 'email']]`
- `it rejects invalid password` — postJson with wrong password → assertUnprocessable, assertJsonValidationErrors(['email'])
- `it rejects nonexistent email` — postJson with unknown email → assertUnprocessable, assertJsonValidationErrors(['email'])
- `it rejects missing fields` — postJson empty → assertUnprocessable, assertJsonValidationErrors(['email', 'password'])
- `it rate limits login attempts` — loop 6 postJson calls with wrong password → last one assertStatus(429)

### Task 2.6 — Write LogoutControllerTest

Create `tests/Feature/Http/Controllers/Api/Auth/LogoutControllerTest.php`:
- `it revokes current token` — create user, create token via `$user->createToken('test')`, use `withHeader('Authorization', 'Bearer '.$token)` to call postJson `/api/logout` → assertOk. Then call getJson `/api/user` with same token → assertUnauthorized
- `it rejects unauthenticated logout` — postJson `/api/logout` without auth → assertUnauthorized

---

## Phase 3: Protect Write Endpoints

**Goal:** All store/update/destroy routes require `auth:sanctum`. Read routes and contact forms remain public.

**Verify:** `php artisan test tests/Feature/Http/Controllers/Api/Auth/ProtectedRoutesTest.php`

### Task 3.1 — Restructure products.php routes

Edit `routes/api/products.php`. Move GET routes out of the protected group. Wrap POST, PATCH, DELETE in `Route::middleware('auth:sanctum')->group(...)`.

### Task 3.2 — Restructure categories.php routes

Same pattern as 3.1 for `routes/api/categories.php`.

### Task 3.3 — Restructure pages.php routes

Same pattern for `routes/api/pages.php`. Keep `{page:slug}` binding.

### Task 3.4 — Restructure galleries.php routes

Same pattern for `routes/api/galleries.php`. Keep `{gallery:slug}` binding.

### Task 3.5 — Restructure users.php routes

Same pattern for `routes/api/users.php`.

### Task 3.6 — Write ProtectedRoutesTest

Create `tests/Feature/Http/Controllers/Api/Auth/ProtectedRoutesTest.php`:
- `it rejects unauthenticated product creation` — postJson `/api/products` with valid data, no auth → assertUnauthorized
- `it rejects unauthenticated category creation` — postJson `/api/categories`, no auth → assertUnauthorized
- `it rejects unauthenticated page creation` — postJson `/api/pages`, no auth → assertUnauthorized
- `it rejects unauthenticated gallery creation` — postJson `/api/galleries`, no auth → assertUnauthorized
- `it rejects unauthenticated user creation` — postJson `/api/users`, no auth → assertUnauthorized
- `it allows public product listing` — get `/api/products`, no auth → assertOk
- `it allows public contact form submission` — postJson `/api/contact/creation` with valid data → assertStatus is not 401

---

## Phase 4: Update Existing Tests

**Goal:** All 102 existing tests pass again after auth middleware was added.

**Verify:** `php artisan test` — all tests green.

### Task 4.1 — Add actingAs to Store test files

Add `beforeEach` with `$this->actingAs(User::factory()->create())` to these 5 files:
- `tests/Feature/Http/Controllers/Api/User/StoreUserControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Category/StoreCategoryControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Product/StoreProductControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Page/StorePageControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Gallery/StoreGalleryControllerTest.php`

Also add `use App\Models\User;` import where not already present.

### Task 4.2 — Add actingAs to Update test files

Same pattern for these 4 files:
- `tests/Feature/Http/Controllers/Api/User/UpdateUserControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Category/UpdateCategoryControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Product/UpdateProductControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Gallery/UpdateGalleryControllerTest.php`

### Task 4.3 — Add actingAs to Destroy test files

Same pattern for these 5 files:
- `tests/Feature/Http/Controllers/Api/User/DestroyUserControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Category/DestroyCategoryControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Product/DestroyProductControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Page/DestroyPageControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Gallery/DestroyGalleryControllerTest.php`

Note: Contact form tests and read-only tests (Index, Show) require NO changes.

### Task 4.4 — Run full test suite

Run `php artisan test`. All existing 102 tests + new auth tests must pass. Fix any failures.
