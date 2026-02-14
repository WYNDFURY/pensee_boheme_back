# Admin Authentication & Back Office — Design

## Overview

Add Sanctum token-based authentication to protect write endpoints. Two new controllers (login, logout), route
restructuring to split public/protected routes, middleware fix, admin seeder, and test suite updates.

No new database tables — Sanctum's `personal_access_tokens` table already exists. No new models. No service layer
needed.

## Architecture

```
Request flow:

Public (GET, contact POST) Protected (store/update/destroy)
┌──────────────────────┐ ┌──────────────────────────────┐
│ No auth middleware │ │ auth:sanctum middleware │
│ throttle:60,1 │ │ throttle:60,1 │
└──────┬───────────────┘ └──────┬───────────────────────┘
│ │
▼ ▼
Controller → Response Token lookup in
personal_access_tokens
│
┌──────┴──────┐
│ Valid │ Invalid
▼ ▼
Controller 401 JSON
→ Response

Login flow:
POST /api/login ──► validate ──► check credentials ──► create token ──► return token + user
│ │
422 401
(missing fields) (bad credentials)
```

## Components and Interfaces

### New Files

#### 1. `app/Http/Controllers/Auth/LoginController.php`

```php
class LoginController
{
public function __invoke(Request $request): JsonResponse
{
$validated = $request->validate([
'email' => 'required|string|email',
'password' => 'required|string',
]);

$user = User::where('email', $validated['email'])->first();

if (! $user || ! Hash::check($validated['password'], $user->password)) {
throw ValidationException::withMessages([
'email' => ['Invalid credentials.'],
]);
}

$token = $user->createToken('admin-token')->plainTextToken;

return response()->json([
'token' => $token,
'user' => [
'id' => $user->id,
'first_name' => $user->first_name,
'last_name' => $user->last_name,
'email' => $user->email,
],
]);
}
}
```

**Notes:**
- Uses `ValidationException` for invalid credentials → returns 422 (same shape as validation errors). This avoids
leaking whether email or password was wrong while keeping consistent error format.
- `createToken('admin-token')` — single ability set, no scoping needed for single-admin.

#### 2. `app/Http/Controllers/Auth/LogoutController.php`

```php
class LogoutController
{
public function __invoke(Request $request): JsonResponse
{
$request->user()->currentAccessToken()->delete();

return response()->json(['message' => 'Logged out']);
}
}
```

#### 3. `routes/api/auth.php`

```php
Route::post('/login', LoginController::class)->middleware('throttle:5,1')->name('login');
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum')->name('logout');
```

**Notes:**
- Login gets its own `throttle:5,1` (5 requests per minute) — separate from the global `throttle:60,1`.
- Logout requires `auth:sanctum`.

#### 4. `database/seeders/AdminUserSeeder.php`

```php
class AdminUserSeeder extends Seeder
{
public function run(): void
{
User::firstOrCreate(
['email' => env('ADMIN_EMAIL', 'admin@pensee-boheme.fr')],
[
'first_name' => env('ADMIN_FIRST_NAME', 'Admin'),
'last_name' => env('ADMIN_LAST_NAME', 'Pensée Bohème'),
'email' => env('ADMIN_EMAIL', 'admin@pensee-boheme.fr'),
'password' => Hash::make(env('ADMIN_PASSWORD')),
]
);
}
}
```

### Modified Files

#### 5. Route files — split public/protected

Each route file in `routes/api/` gets restructured to separate GET routes (public) from write routes (protected).
Pattern for every resource:

```php
// routes/api/products.php — example pattern applied to all 5 resources
Route::prefix('products')->name('products.')->group(function () {
// Public
Route::get('/', IndexProductController::class)->name('index');
Route::get('/{product}', ShowProductController::class)->name('show');

// Protected
Route::middleware('auth:sanctum')->group(function () {
Route::post('/', StoreProductController::class)->name('store');
Route::patch('/{product}', UpdateProductController::class)->name('update');
Route::delete('/{product}', DestroyProductController::class)->name('destroy');
});
});
```

**Files to modify with this pattern:**
- `routes/api/products.php`
- `routes/api/categories.php`
- `routes/api/users.php`
- `routes/api/pages.php` (uses `{page:slug}`)
- `routes/api/galleries.php` (uses `{gallery:slug}`)

**Files NOT modified** (stay fully public):
- `routes/api/instagram.php` — read-only
- `routes/api/contact.php` — public form submissions

#### 6. `routes/api.php`

Add `require __DIR__.'/api/auth.php';` to the route group.

#### 7. `app/Http/Middleware/Authenticate.php`

Change `redirectTo` to always return `null` for API requests:

```php
protected function redirectTo(Request $request): ?string
{
if ($request->expectsJson()) {
return null;
}

return null; // API-only app, never redirect
}
```

This ensures unauthenticated API requests always get 401 JSON, even if the request doesn't have `Accept:
application/json` header.

#### 8. `database/seeders/DatabaseSeeder.php`

Add `AdminUserSeeder::class` to the `$this->call()` array.


### Existing Test Updates

All feature tests that call protected endpoints must authenticate. Add `actingAs` with a factory user in a `beforeEach`
hook or per-test.

**15 test files need `actingAs()`:**

Store tests (5):
- `tests/Feature/Http/Controllers/Api/User/StoreUserControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Category/StoreCategoryControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Product/StoreProductControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Page/StorePageControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Gallery/StoreGalleryControllerTest.php`

Update tests (4):
- `tests/Feature/Http/Controllers/Api/User/UpdateUserControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Category/UpdateCategoryControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Product/UpdateProductControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Gallery/UpdateGalleryControllerTest.php`

Destroy tests (5):
- `tests/Feature/Http/Controllers/Api/User/DestroyUserControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Category/DestroyCategoryControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Product/DestroyProductControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Page/DestroyPageControllerTest.php`
- `tests/Feature/Http/Controllers/Api/Gallery/DestroyGalleryControllerTest.php`

**Update page** — Pest `actingAs` pattern:

```php
use App\Models\User;
use function Pest\Laravel\actingAs;

// In each test or in beforeEach:
$user = User::factory()->create();
actingAs($user);
```

**Contact form tests remain unchanged** — those endpoints stay public.

## Data Models

No new models or migrations.

**Existing tables used:**
- `users` — admin user (already exists, no schema change)
- `personal_access_tokens` — Sanctum token storage (already migrated by Sanctum install)

**Seeder data:**
- Single admin user created from `.env` vars via `AdminUserSeeder`

## Error Handling

| Scenario | Status | Response Body |
|---|---|---|
| Login with missing fields | 422 | `{"message": "...", "errors": {"email": [...], "password": [...]}}` |
| Login with wrong credentials | 422 | `{"message": "...", "errors": {"email": ["Invalid credentials."]}}` |
| Request to protected route without token | 401 | `{"message": "Unauthenticated."}` |
| Request with revoked/invalid token | 401 | `{"message": "Unauthenticated."}` |
| Login rate limit exceeded | 429 | `{"message": "Too Many Attempts."}` (Laravel default) |
| Logout success | 200 | `{"message": "Logged out"}` |

**Design decision:** Invalid credentials return 422 via `ValidationException` (not 401). This keeps the error format
identical to validation errors and avoids the need for a custom exception handler. The generic "Invalid credentials"
message on the `email` field prevents enumeration.

## Testing Strategy

### New Test Files

#### `tests/Feature/Http/Controllers/Api/Auth/LoginControllerTest.php`

Tests:
- `it returns token with valid credentials` — create user, postJson login → 200, assertJsonStructure(['token', 'user'])
- `it rejects invalid password` — create user, postJson wrong password → 422, assertJsonValidationErrors(['email'])
- `it rejects nonexistent email` — postJson unknown email → 422, same error shape
- `it rejects missing fields` — postJson empty → 422, assertJsonValidationErrors(['email', 'password'])
- `it rate limits login attempts` — 6 rapid postJson calls → 5th or 6th returns 429

#### `tests/Feature/Http/Controllers/Api/Auth/LogoutControllerTest.php`

Tests:
- `it revokes current token` — login, use token to logout → 200, then GET /api/user with same token → 401
- `it rejects unauthenticated logout` — postJson /api/logout without token → 401

#### `tests/Feature/Http/Controllers/Api/Auth/ProtectedRoutesTest.php`

Tests (one per resource to verify middleware is applied):
- `it rejects unauthenticated product creation` — postJson /api/products without auth → 401
- `it rejects unauthenticated category creation` — postJson /api/categories without auth → 401
- `it rejects unauthenticated page creation` — postJson /api/pages without auth → 401
- `it rejects unauthenticated gallery creation` — postJson /api/galleries without auth → 401
- `it rejects unauthenticated user creation` — postJson /api/users without auth → 401
- `it allows public product listing` — get /api/products → 200 (no auth)
- `it allows public contact form submission` — postJson /api/contact/creation → not 401

### Existing Test Updates

Add `actingAs(User::factory()->create())` to the 15 test files listed above. Use Pest `beforeEach` per file to avoid
repetition:

```php
beforeEach(function () {
$this->actingAs(User::factory()->create());
});
```

## Performance Considerations

- `auth:sanctum` adds one DB query per protected request (lookup in `personal_access_tokens` by hashed token).
Negligible overhead.
- Login rate limiter uses Laravel's built-in cache-based `ThrottleRequests` — no additional storage.
- No changes to public read endpoints — zero performance impact on SSG builds.

## Security Considerations

- **Brute force protection**: `throttle:5,1` on login (5 attempts/min per IP)
- **Credential enumeration prevention**: Same error message for wrong email and wrong password
- **Token storage**: Sanctum stores tokens hashed (SHA-256) in DB — plain text only returned once at creation
- **Token transmission**: Always via `Authorization: Bearer` header, never in URLs
- **CORS**: Already configured with `supports_credentials: true` and explicit origin whitelist
- **No token expiration**: Acceptable for single-admin use case — logout explicitly revokes. If needed later, set
`sanctum.expiration` config value.

## Monitoring and Observability

No dedicated monitoring needed for this feature. Laravel's default logging covers:
- Failed login attempts logged via `Illuminate\Auth\Events\Failed` event (Laravel default)
- Rate limit hits logged by middleware
- 401/422 responses visible in access logs

If needed later, a listener on `Illuminate\Auth\Events\Failed` can send notifications for repeated failed attempts.
