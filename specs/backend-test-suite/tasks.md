# Backend Test Suite — Implementation Plan

## Phase 1: Infrastructure & Bug Fixes

**Goal:** Test infrastructure runs correctly — `php artisan test` executes with RefreshDatabase and all factories work.

**Verify:** `php artisan test` runs with zero tests and no errors.

### Task 1.1 — Fix DestroyProductController bug
Remove `dd($medias)` and the unused `$medias` variable on lines 11-12 of `app/Http/Controllers/Product/DestroyProductController.php`. Keep only `$product->delete()`.

### Task 1.2 — Update tests/Pest.php
- Uncomment `RefreshDatabase::class` in the `uses()` call for Feature tests
- Add a second `uses()` block for Unit tests with `TestCase` and `RefreshDatabase`
- Remove the `toBeOne` expectation extension
- Remove the `something()` placeholder function

### Task 1.3 — Create missing factories
Create 3 new factory files:

**database/factories/InstagramAccessTokenFactory.php:**
- `access_token`: `encrypt('fake-access-token-' . uuid)`
- `expires_at`: `now()->addMonths(3)`

**database/factories/InstagramMediaFactory.php:**
- `media_id`: unique uuid
- `caption`: sentence
- `media_type`: random `['IMAGE', 'CAROUSEL_ALBUM']`
- `media_url`: url
- `permalink`: url
- `timestamp`: dateTimeBetween('-1 year', 'now')

**database/factories/ProductOptionFactory.php:**
- `name`, `slug`, `description`, `price`, `has_price: true`, `is_active: true`
- `product_id`: `Product::factory()`

### Task 1.4 — Clean up old test files and folders
- Delete `tests/Feature/BasicTest.php`
- Delete `tests/Feature/mail/MailTest.php` and remove `tests/Feature/mail/` directory
- Rename `tests/Feature/Http/Controllers/Api/Categories/` → `Category/`
- Rename `tests/Feature/Http/Controllers/Api/Users/` → `User/`
- Create empty directories: `tests/Feature/Http/Controllers/Api/Page/`, `Gallery/`, `InstagramMedia/`, `Contact/`, `tests/Feature/Console/`, `tests/Unit/Models/`, `tests/Unit/Services/`

---

## Phase 2: User CRUD Tests

**Goal:** Full coverage of User endpoints with validation errors, happy paths, and edge cases.

**Verify:** `php artisan test tests/Feature/Http/Controllers/Api/User/`

### Task 2.1 — Rewrite IndexUserControllerTest.php
Tests:
- `it returns all users` — create 3 via factory, GET `/api/users`, assertOk, assertJsonCount(3)
- `it returns empty array when no users exist` — GET `/api/users`, assertOk, assertJsonCount(0)

### Task 2.2 — Rewrite StoreUserControllerTest.php
Tests:
- `it creates a user with valid data` — POST with first_name, last_name, email, password → assertCreated, assertDatabaseHas
- `it hashes password on creation` — POST, then fetch user from DB, `Hash::check()` returns true
- `it rejects missing required fields` — POST empty → assertUnprocessable, assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password'])
- `it rejects duplicate email` — create user, POST same email → 422 with email error
- `it rejects password under 8 characters` — POST with 'short' → 422 with password error
- `it rejects invalid email format` — POST with 'not-email' → 422 with email error

### Task 2.3 — Rewrite ShowUserControllerTest.php
Tests:
- `it returns a user` — create via factory, GET `/api/users/{id}`, assertOk, assertJsonPath for id/first_name/last_name/email
- `it returns 404 for nonexistent user` — GET `/api/users/9999`, assertNotFound

### Task 2.4 — Rewrite UpdateUserControllerTest.php
Tests:
- `it updates a user` — create via factory, PATCH with new first_name → assertOk, assertDatabaseHas
- `it allows partial update` — PATCH only email, verify first_name unchanged
- `it hashes password when updated` — PATCH with new password, verify Hash::check
- `it rejects duplicate email on another user` — create 2 users, PATCH user2 with user1's email → 422

### Task 2.5 — Rewrite DestroyUserControllerTest.php
Tests:
- `it soft deletes a user` — create, DELETE → assertOk, assertSoftDeleted
- `it returns 404 for nonexistent user` — DELETE `/api/users/9999` → assertNotFound

---

## Phase 3: Page CRUD Tests

**Goal:** Full coverage of Page endpoints including slug-based routing and nested relationship loading.

**Verify:** `php artisan test tests/Feature/Http/Controllers/Api/Page/`

### Task 3.1 — Create IndexPageControllerTest.php
Tests:
- `it returns all pages` — create 3, GET `/api/pages`, assertOk, assertJsonCount(3)
- `it returns empty array when no pages exist` — assertJsonCount(0)

### Task 3.2 — Create StorePageControllerTest.php
Tests:
- `it creates a page with valid data` — POST `{slug: 'new-page'}` → assertCreated, assertDatabaseHas
- `it rejects missing slug` — POST empty → 422, assertJsonValidationErrors(['slug'])
- `it rejects duplicate slug` — create page, POST same slug → 422

### Task 3.3 — Create ShowPageControllerTest.php
Tests:
- `it returns a page by slug` — create, GET `/api/pages/{slug}` → assertOk, assertJsonPath('data.slug', ...)
- `it returns page with nested categories and active products` — create page → category → active product + inactive product → GET, verify `data.categories` count and `data.categories.0.products` contains only active
- `it returns 404 for nonexistent slug` — GET `/api/pages/nonexistent` → assertNotFound

### Task 3.4 — Create UpdatePageControllerTest.php
Tests:
- `it updates a page slug` — PATCH `/api/pages/{slug}` with new slug → assertOk, assertDatabaseHas

### Task 3.5 — Create DestroyPageControllerTest.php
Tests:
- `it soft deletes a page` — DELETE `/api/pages/{slug}` → assertOk, assertSoftDeleted
- `it returns 404 for nonexistent slug` — DELETE `/api/pages/nope` → assertNotFound

---

## Phase 4: Category CRUD Tests

**Goal:** Full coverage of Category endpoints with FK validation.

**Verify:** `php artisan test tests/Feature/Http/Controllers/Api/Category/`

### Task 4.1 — Rewrite IndexCategoryControllerTest.php
Tests:
- `it returns all categories` — create 3, assertJsonCount(3)
- `it returns empty array when none exist` — assertJsonCount(0)

### Task 4.2 — Rewrite StoreCategoryControllerTest.php
Tests:
- `it creates a category with valid data` — create Page first, POST with name + page_id → assertCreated, assertDatabaseHas
- `it rejects missing required fields` — POST empty → 422 with errors on name, page_id
- `it rejects nonexistent page_id` — POST with page_id: 9999 → 422

### Task 4.3 — Rewrite ShowCategoryControllerTest.php
Tests:
- `it returns a category` — assertOk, assertJsonPath for id/name
- `it returns 404 for nonexistent category` — assertNotFound

### Task 4.4 — Rewrite UpdateCategoryControllerTest.php
Tests:
- `it updates a category` — PATCH name → assertOk, assertDatabaseHas
- `it allows partial update` — PATCH description only, verify name unchanged
- `it rejects nonexistent page_id` — PATCH page_id: 9999 → 422

### Task 4.5 — Rewrite DestroyCategoryControllerTest.php
Tests:
- `it soft deletes a category` — assertSoftDeleted
- `it returns 404 for nonexistent category` — assertNotFound

---

## Phase 5: Product CRUD Tests

**Goal:** Full coverage of Product endpoints including slug uniqueness, price validation, and resource response format.

**Verify:** `php artisan test tests/Feature/Http/Controllers/Api/Product/`

### Task 5.1 — Rewrite IndexProductControllerTest.php
Tests:
- `it returns all products` — create 3, GET `/api/products`, assertOk, assertJsonCount(3)
- `it returns empty array when none exist` — assertJsonCount(0)

### Task 5.2 — Rewrite StoreProductControllerTest.php
Tests:
- `it creates a product with valid data` — POST with name, slug, category_id, price, is_active, has_price → assertCreated, assertJsonPath('product.slug'), assertDatabaseHas
- `it rejects missing required fields` — POST empty → 422 on name, slug, category_id
- `it rejects duplicate slug` — create product, POST same slug → 422
- `it rejects nonexistent category_id` — POST category_id: 9999 → 422
- `it rejects negative price` — POST price: -5 → 422

### Task 5.3 — Rewrite ShowProductControllerTest.php
Tests:
- `it returns a product with relationships` — create product with category, GET → assertOk, verify data.name, data.category_id present
- `it returns 404 for nonexistent product` — assertNotFound

### Task 5.4 — Rewrite UpdateProductControllerTest.php
Tests:
- `it updates a product` — PATCH name, slug → assertOk, assertDatabaseHas
- `it allows partial update` — PATCH only name, verify slug unchanged
- `it rejects duplicate slug on another product` — create 2 products, PATCH product2 slug to product1's slug → 422

### Task 5.5 — Rewrite DestroyProductControllerTest.php
Tests (depends on Task 1.1 bug fix):
- `it soft deletes a product` — DELETE → assertOk, assertSoftDeleted
- `it returns 404 for nonexistent product` — assertNotFound

---

## Phase 6: Gallery CRUD Tests

**Goal:** Full coverage of Gallery endpoints with slug-based routing and media filtering behavior.

**Verify:** `php artisan test tests/Feature/Http/Controllers/Api/Gallery/`

### Task 6.1 — Create IndexGalleryControllerTest.php
Tests:
- `it excludes galleries without media` — create 3 galleries (no media attached), GET `/api/galleries` → assertOk, assertJsonCount(0)
- `it returns empty array when no galleries exist` — assertJsonCount(0)

### Task 6.2 — Create StoreGalleryControllerTest.php
Tests:
- `it creates a gallery with valid data` — POST name, slug → assertCreated, assertDatabaseHas
- `it rejects missing required fields` — POST empty → 422 on name, slug
- `it rejects duplicate slug` — create gallery, POST same slug → 422

### Task 6.3 — Create ShowGalleryControllerTest.php
Tests:
- `it returns a gallery by slug` — create, GET `/api/galleries/{slug}` → assertOk, assertJsonPath('data.slug')
- `it returns 404 for nonexistent slug` — assertNotFound

### Task 6.4 — Create UpdateGalleryControllerTest.php
Tests:
- `it updates a gallery` — PATCH name → assertOk, assertDatabaseHas
- `it allows partial update` — PATCH description only, verify name unchanged

### Task 6.5 — Create DestroyGalleryControllerTest.php
Tests:
- `it soft deletes a gallery` — DELETE `/api/galleries/{slug}` → assertOk, assertSoftDeleted
- `it returns 404 for nonexistent slug` — assertNotFound

---

## Phase 7: Instagram Media & Contact Form Tests

**Goal:** Coverage for Instagram endpoint and both contact form endpoints including mail assertions and spam protection.

**Verify:** `php artisan test tests/Feature/Http/Controllers/Api/InstagramMedia/ tests/Feature/Http/Controllers/Api/Contact/`

### Task 7.1 — Create IndexInstagramMediaControllerTest.php
Tests:
- `it returns at most 12 media sorted by timestamp desc` — create 15 InstagramMedia records with varying timestamps, GET `/api/instagram` → assertOk, count ≤ 12, verify descending order
- `it returns empty array when no media exists` — assertJsonCount(0)

### Task 7.2 — Create CreationContactFormControllerTest.php
Uses `Mail::fake()` in each test.
Tests:
- `it sends creation contact email with valid data` — postJson with all fields + empty honeypot → assertOk, Mail::assertSent(CreationContactFormMail)
- `it rejects missing required fields` — postJson empty → 422, assertJsonValidationErrors for firstName, lastName, email, phone, message. Mail::assertNothingSent
- `it rejects invalid email format` — postJson with email: 'bad' → 422
- `it rejects spam via honeypot` — postJson with additional_info filled → 422, assertJson message 'Spam detected', Mail::assertNothingSent

### Task 7.3 — Create EventContactFormControllerTest.php
Uses `Mail::fake()` in each test.
Tests:
- `it sends event contact email with valid data` — postJson with all fields (firstName, lastName, email, phone, eventDate, eventLocation, themeColors, message, additional_info: '') → assertOk, Mail::assertSent(EventContactFormMail)
- `it rejects missing required fields` — postJson empty → 422, assertJsonValidationErrors for all required fields. Mail::assertNothingSent
- `it rejects invalid email format` — 422
- `it rejects invalid date format for eventDate` — postJson with eventDate: 'not-a-date' → 422
- `it rejects spam via honeypot` — 422, Mail::assertNothingSent

---

## Phase 8: Model Unit Tests

**Goal:** Verify all model relationships, accessors, and traits.

**Verify:** `php artisan test tests/Unit/Models/`

### Task 8.1 — Create UserTest.php
Tests:
- `it returns full name via accessor` — create with first_name 'Marie', last_name 'Dupont' → expect full_name = 'Marie Dupont'
- `it uses soft deletes` — delete, assertSoftDeleted, withTrashed finds it

### Task 8.2 — Create PageTest.php
Tests:
- `it has many categories` — create page + 3 categories with page_id → expect categories count 3
- `it uses soft deletes`

### Task 8.3 — Create CategoryTest.php
Tests:
- `it belongs to a page` — create category (factory auto-creates page) → expect page is instanceof Page
- `it has many products` — create category + 2 products → expect products count 2
- `it uses soft deletes`

### Task 8.4 — Create ProductTest.php
Tests:
- `it belongs to a category` — expect category instanceof Category
- `it has many options` — create product + 2 ProductOptions → expect options count 2
- `it uses soft deletes`
- `it casts is_active and has_price to boolean`

### Task 8.5 — Create ProductOptionTest.php
Tests:
- `it belongs to a product` — expect product instanceof Product
- `it uses soft deletes`

### Task 8.6 — Create GalleryTest.php
Tests:
- `it uses soft deletes`
- `it casts is_published to boolean`

### Task 8.7 — Create InstagramMediaTest.php
Tests:
- `it can be created via factory` — create, assertDatabaseHas

### Task 8.8 — Create InstagramAccessTokenTest.php
Tests:
- `it can be created via factory` — create, assertDatabaseHas
- `it uses soft deletes`

---

## Phase 9: Service Unit Tests

**Goal:** Verify Instagram services and token refresh services with mocked HTTP and DB.

**Verify:** `php artisan test tests/Unit/Services/`

### Task 9.1 — Create FetchInstagramMediaServiceTest.php
`beforeEach`: seed InstagramAccessToken (id=1, encrypted token), set config values for meta_app_id, meta_app_secret, instagram_account_id.
Tests:
- `it fetches and filters media by type` — Http::fake with 3 items (IMAGE, VIDEO, CAROUSEL_ALBUM) → service returns only IMAGE + CAROUSEL_ALBUM
- `it returns max 12 results` — Http::fake with 20 IMAGE items → result has 12

### Task 9.2 — Create StoreInstagramMediaServiceTest.php
Tests:
- `it creates instagram media records` — pass array of 2 media items → assertDatabaseCount 2, assertDatabaseHas media_id

### Task 9.3 — Create StartStoringInstagramMediaServiceTest.php
`beforeEach`: seed InstagramAccessToken, set config.
Tests:
- `it clears existing media and stores new batch` — pre-create 3 InstagramMedia, Http::fake with 2 items → assertDatabaseCount 2 (old cleared)

### Task 9.4 — Create RefreshLongLivedTokenServiceTest.php
`beforeEach`: seed InstagramAccessToken, set config.
Tests:
- `it calls graph API to refresh token` — Http::fake returning `{access_token: 'new-token'}` → service returns array with access_token

### Task 9.5 — Create UpdatesLongLivedTokenServiceTest.php
Tests:
- `it updates token in database` — create InstagramAccessToken, call service with `{access_token: 'new-value'}` → verify DB record updated, expires_at ~3 months from now

### Task 9.6 — Create StartRefreshingLongLivedTokenServiceTest.php
`beforeEach`: seed InstagramAccessToken, set config.
Tests:
- `it orchestrates refresh and update` — Http::fake → call service → verify DB token updated

---

## Phase 10: Console Command Tests

**Goal:** Verify artisan commands invoke their services correctly.

**Verify:** `php artisan test tests/Feature/Console/`

### Task 10.1 — Create FetchAndStoreInstagramMediasTest.php
Mock `StartStoringInstagramMediaService` via app container. Test:
- `it runs the command successfully` — artisan('app:fns-instagram-medias') → expectsOutput, assertExitCode(0), mock received call

### Task 10.2 — Create RefreshTokenTest.php
Mock `StartRefreshingLongLivedTokenService` via app container. Test:
- `it runs the command successfully` — artisan('app:refresh-token') → expectsOutput, assertExitCode(0), mock received call

---

## Phase 11: Full Suite Validation

**Goal:** Entire test suite passes with no failures.

**Verify:** `php artisan test` — all ~120 tests green.

### Task 11.1 — Run full suite and fix failures
Run `php artisan test`. Fix any assertion mismatches (response shape differences, JSON path issues). Re-run until all pass.

### Task 11.2 — Run with parallel execution
Run `php artisan test --parallel`. Verify no test isolation issues.
