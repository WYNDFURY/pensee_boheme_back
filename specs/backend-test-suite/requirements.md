# Backend Test Suite — Requirements

## Introduction

Comprehensive Pest PHP feature and unit test suite covering all API endpoints, services, models, mail, and console commands of the Pensée Bohème backend. The existing tests are incomplete (only Products, Categories, Users CRUD and basic mail tests exist) and lack validation error cases, edge cases, and coverage for Pages, Galleries, Instagram, and Services. This spec defines what a complete, clean test suite looks like.

## Alignment with Product Vision

Pensée Bohème is an e-commerce/portfolio platform for floral arrangements and event decoration. Reliability of the API is critical: the storefront depends on pages/categories/products/galleries endpoints, contact forms drive business leads, and Instagram integration provides social proof. A robust test suite ensures regressions are caught before deployment.

## Current State

### Existing tests (to rewrite/clean up)
- `tests/Feature/Http/Controllers/Api/Product/` — 5 CRUD tests (minimal assertions)
- `tests/Feature/Http/Controllers/Api/Category/` — 5 CRUD tests (StoreCategoryController test sends incomplete data — missing required `page_id`)
- `tests/Feature/Http/Controllers/Api/User/` — 5 CRUD tests (minimal assertions)
- `tests/Feature/mail/MailTest.php` — 5 mail tests
- `tests/Feature/BasicTest.php` — 1 media collection test
- `tests/Unit/` — empty

### Problems with existing tests
- Minimal assertions: many tests only check status code and a single JSON path
- Missing validation error scenarios
- StoreCategoryController test does not send required `page_id`
- No tests for Pages, Galleries, Instagram endpoints
- No service layer tests
- No model relationship tests
- No console command tests
- `RefreshDatabase` trait commented out in Pest.php — must be enabled

## Requirements

### R1 — Test Infrastructure Setup

**As a** developer, **I want** the test configuration properly set up, **so that** tests run reliably in isolation.

**Acceptance Criteria:**
- `tests/Pest.php` enables `RefreshDatabase` trait for Feature tests
- Remove the placeholder `something()` function and unused `toBeOne` expectation
- All tests use Pest syntax (`it()`, `test()`, `beforeEach()`)
- Helper functions defined in `tests/Pest.php` if shared across tests (e.g. `createAuthenticatedUser()`)

---

### R2 — User CRUD Tests

**As a** developer, **I want** full test coverage for User endpoints, **so that** user management works correctly.

**Endpoint coverage:**

| Method | Route | Controller |
|--------|-------|------------|
| GET | `/api/users` | IndexUserController |
| POST | `/api/users` | StoreUserController |
| GET | `/api/users/{user}` | ShowUserController |
| PATCH | `/api/users/{user}` | UpdateUserController |
| DELETE | `/api/users/{user}` | DestroyUserController |

**Acceptance Criteria:**
- **Index**: returns 200 with correct count, returns empty array when no users
- **Store**: returns 201 with all fields, persists to database, hashes password, rejects missing `first_name`/`last_name`/`email`/`password` (422), rejects duplicate email (422), rejects password < 8 chars (422), rejects invalid email format (422)
- **Show**: returns 200 with correct user data, returns 404 for nonexistent user
- **Update**: returns 200 with updated fields, persists changes, allows partial update (single field), rejects duplicate email on another user (422), hashes password when updated
- **Destroy**: returns 200, soft deletes user (assertSoftDeleted), returns 404 for nonexistent user

---

### R3 — Page CRUD Tests

**As a** developer, **I want** full test coverage for Page endpoints, **so that** page management works correctly.

**Endpoint coverage:**

| Method | Route | Controller |
|--------|-------|------------|
| GET | `/api/pages` | IndexPageController |
| POST | `/api/pages` | StorePageController |
| GET | `/api/pages/{page:slug}` | ShowPageController |
| PATCH | `/api/pages/{page:slug}` | UpdatePageController |
| DELETE | `/api/pages/{page:slug}` | DestroyPageController |

**Acceptance Criteria:**
- **Index**: returns 200 with correct count
- **Store**: returns 201, persists to database, rejects missing `slug` (422), rejects duplicate slug (422)
- **Show**: returns 200 with nested categories (ordered by `order`), categories include active products (ordered by name) with media, returns 404 for nonexistent slug
- **Update**: returns 200 with updated slug, persists changes
- **Destroy**: returns 200, soft deletes page, returns 404 for nonexistent slug

**Note:** Routes use slug binding (`{page:slug}`), not ID.

---

### R4 — Category CRUD Tests

**As a** developer, **I want** full test coverage for Category endpoints, **so that** category management works correctly.

**Endpoint coverage:**

| Method | Route | Controller |
|--------|-------|------------|
| GET | `/api/categories` | IndexCategoryController |
| POST | `/api/categories` | StoreCategoryController |
| GET | `/api/categories/{category}` | ShowCategoryController |
| PATCH | `/api/categories/{category}` | UpdateCategoryController |
| DELETE | `/api/categories/{category}` | DestroyCategoryController |

**Acceptance Criteria:**
- **Index**: returns 200 with correct count
- **Store**: returns 201, persists to database, requires `name` and `page_id`, rejects missing required fields (422), rejects nonexistent `page_id` (422)
- **Show**: returns 200 with correct category, returns 404 for nonexistent category
- **Update**: returns 200, persists changes, allows partial update, rejects nonexistent `page_id` (422)
- **Destroy**: returns 200, soft deletes, returns 404 for nonexistent category

---

### R5 — Product CRUD Tests

**As a** developer, **I want** full test coverage for Product endpoints, **so that** product management works correctly.

**Endpoint coverage:**

| Method | Route | Controller |
|--------|-------|------------|
| GET | `/api/products` | IndexProductController |
| POST | `/api/products` | StoreProductController |
| GET | `/api/products/{product}` | ShowProductController |
| PATCH | `/api/products/{product}` | UpdateProductController |
| DELETE | `/api/products/{product}` | DestroyProductController |

**Acceptance Criteria:**
- **Index**: returns 200 with ProductResource collection, includes category relationship
- **Store**: returns 201, persists to database, requires `name`, `slug`, `category_id`, rejects missing required fields (422), rejects duplicate slug (422), rejects nonexistent `category_id` (422), rejects negative price (422)
- **Show**: returns 200 with ProductResource (media, options, category loaded), returns 404 for nonexistent product
- **Update**: returns 200, persists changes, allows partial update, rejects duplicate slug on another product (422)
- **Destroy**: returns 200, soft deletes product, returns 404 for nonexistent product

---

### R6 — Gallery CRUD Tests

**As a** developer, **I want** full test coverage for Gallery endpoints, **so that** gallery management works correctly.

**Endpoint coverage:**

| Method | Route | Controller |
|--------|-------|------------|
| GET | `/api/galleries` | IndexGalleryController |
| POST | `/api/galleries` | StoreGalleryController |
| GET | `/api/galleries/{gallery:slug}` | ShowGalleryController |
| PATCH | `/api/galleries/{gallery:slug}` | UpdateGalleryController |
| DELETE | `/api/galleries/{gallery:slug}` | DestroyGalleryController |

**Acceptance Criteria:**
- **Index**: returns 200 with GalleryResource collection, sorted by ID descending, only includes galleries with media
- **Store**: returns 201, persists to database, requires `name` and `slug`, rejects missing required fields (422), rejects duplicate slug (422)
- **Show**: returns 200 with GalleryResource including media, returns 404 for nonexistent slug
- **Update**: returns 200, persists changes, allows partial update
- **Destroy**: returns 200, soft deletes gallery, returns 404 for nonexistent slug

**Note:** Routes use slug binding (`{gallery:slug}`), not ID.

---

### R7 — Instagram Media Tests

**As a** developer, **I want** test coverage for the Instagram endpoint, **so that** the feed displays correctly.

**Endpoint coverage:**

| Method | Route | Controller |
|--------|-------|------------|
| GET | `/api/instagram` | IndexInstagramMediaController |

**Acceptance Criteria:**
- Returns 200 with InstagramMediaResource collection
- Returns at most 12 items
- Items are sorted by timestamp descending (newest first)
- Returns empty array when no media exists

---

### R8 — Contact Form Tests

**As a** developer, **I want** full test coverage for contact form endpoints, **so that** form submissions and spam prevention work correctly.

**Endpoint coverage:**

| Method | Route | Controller |
|--------|-------|------------|
| POST | `/api/contact/creation` | CreationContactFormController |
| POST | `/api/contact/event` | EventContactFormController |

**Acceptance Criteria — Creation Form:**
- Returns 200 and sends `CreationContactFormMail` with valid data
- Rejects missing required fields: `firstName`, `lastName`, `email`, `phone`, `message` (422)
- Rejects invalid email format (422)
- Rejects spam when `additional_info` honeypot is filled (422, mail not sent)
- Returns 500 on mail sending failure (mock exception)

**Acceptance Criteria — Event Form:**
- Returns 200 and sends `EventContactFormMail` with valid data
- Rejects missing required fields: `firstName`, `lastName`, `email`, `phone`, `eventDate`, `eventLocation`, `themeColors`, `message` (422)
- Rejects invalid email format (422)
- Rejects invalid date format for `eventDate` (422)
- Rejects spam when `additional_info` honeypot is filled (422, mail not sent)
- Returns 500 on mail sending failure (mock exception)

---

### R9 — Instagram Service Unit Tests

**As a** developer, **I want** unit tests for Instagram services, **so that** external API integration is verified without real HTTP calls.

**Acceptance Criteria:**

**FetchInstagramMediaService:**
- Fetches media from Graph API, returns filtered array (IMAGE and CAROUSEL_ALBUM only, max 12)
- Handles API error response gracefully (logs error)
- Uses Http::fake() to mock external calls

**StoreInstagramMediaService:**
- Creates InstagramMedia records from fetched data
- Stores all required fields (media_id, caption, media_type, media_url, permalink, timestamp)

**StartStoringInstagramMediaService:**
- Clears existing media before storing new batch
- Orchestrates fetch → store flow

**RefreshLongLivedTokenService:**
- Calls Graph API token refresh endpoint
- Uses Http::fake() to mock external calls

**UpdatesLongLivedTokenService:**
- Updates encrypted token in database
- Sets expires_at to 3 months from now

**StartRefreshingLongLivedTokenService:**
- Orchestrates refresh → update flow

---

### R10 — Console Command Tests

**As a** developer, **I want** tests for artisan commands, **so that** scheduled tasks work correctly.

**Acceptance Criteria:**
- `app:fns-instagram-medias` command executes successfully (calls StartStoringInstagramMediaService)
- `app:refresh-token` command executes successfully (calls StartRefreshingLongLivedTokenService)
- Both commands can be tested via `$this->artisan()` or Pest's `artisan()` helper

---

### R11 — Model Relationship Tests

**As a** developer, **I want** unit tests for model relationships, **so that** the data layer integrity is verified.

**Acceptance Criteria:**
- **Page** hasMany Categories
- **Category** belongsTo Page, hasMany Products
- **Product** belongsTo Category, hasMany ProductOptions
- **ProductOption** belongsTo Product
- **User** has `getFullNameAttribute` accessor returning `"first_name last_name"`
- **Product** and **Gallery** implement HasMedia (Spatie) with WebP conversion registered

---

## Non-Functional Requirements

### Architecture
- Test file structure mirrors controller structure: `tests/Feature/Http/Controllers/Api/{Resource}/`
- Service tests go in `tests/Unit/Services/`
- Model tests go in `tests/Unit/Models/`
- Command tests go in `tests/Feature/Console/`
- Remove `tests/Feature/BasicTest.php` (media collection test moves to model tests)
- Remove `tests/Feature/mail/` (contact form tests move to controller tests)

### Performance
- Use `RefreshDatabase` trait (transactions, not migrate:fresh) for speed
- Use factories for all test data creation
- No real HTTP calls — mock external APIs with `Http::fake()`
- No real mail sending — use `Mail::fake()`

### Reliability
- Each test is independent and idempotent
- No test depends on database state from another test
- No hardcoded IDs

### Security
- Never commit real API keys or tokens in test files
- Use fake/factory data for all test inputs
