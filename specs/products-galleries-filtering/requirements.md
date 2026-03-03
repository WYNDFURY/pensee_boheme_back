# Products & Galleries — Visibility Filtering by Auth Status

## Introduction

The Product and Gallery models already carry visibility flags (`is_active` on Product, `is_published` on Gallery), but the public API currently returns all records regardless of these flags. This feature enforces visibility filtering: unauthenticated (public/frontend) requests only receive active/published items, while authenticated (admin) requests receive all items for content management.

## Alignment with Product Vision

- **Galleries are the primary conversion tool** (product.md goal #1). Serving unpublished galleries to the Nuxt SSG frontend pollutes the portfolio and undermines trust.
- **Admin workflow**: Cécile needs to stage products and galleries (mark as inactive/unpublished) before they are ready for public display. The admin panel must still show all items so she can manage them.
- **SSG context**: The Nuxt frontend consumes the API at build time. Filtering at the API level ensures only published content is baked into the static site — no client-side filtering needed.

## Requirements

### REQ-1: Filter products on index by auth status

**User Story:** As a public visitor, I want to only see active products, so that I am not shown draft or disabled creations.

**Acceptance Criteria:**
- `GET /api/products` without a Bearer token returns only products where `is_active = true`
- `GET /api/products` with a valid Sanctum Bearer token returns all products (active and inactive)
- Response shape and resource structure remain unchanged

### REQ-2: Filter products on show by auth status

**User Story:** As a public visitor, I should not be able to access an inactive product directly, so that draft content stays hidden.

**Acceptance Criteria:**
- `GET /api/products/{product}` without a Bearer token returns 404 if the product has `is_active = false`
- `GET /api/products/{product}` with a valid Sanctum Bearer token returns the product regardless of `is_active`
- Active products remain accessible to everyone

### REQ-3: Filter galleries on index by auth status

**User Story:** As a public visitor, I want to only see published galleries, so that I am not shown incomplete or draft galleries.

**Acceptance Criteria:**
- `GET /api/galleries` without a Bearer token returns only galleries where `is_published = true`
- `GET /api/galleries` with a valid Sanctum Bearer token returns all galleries (published and unpublished)
- Existing filter (exclude galleries with no media) still applies on top of this for public requests
- Response shape and resource structure remain unchanged

### REQ-4: Filter galleries on show by auth status

**User Story:** As a public visitor, I should not be able to access an unpublished gallery directly, so that draft galleries stay hidden.

**Acceptance Criteria:**
- `GET /api/galleries/{gallery:slug}` without a Bearer token returns 404 if the gallery has `is_published = false`
- `GET /api/galleries/{gallery:slug}` with a valid Sanctum Bearer token returns the gallery regardless of `is_published`
- Published galleries remain accessible to everyone

### REQ-5: Conditional filtering in ShowPageController

**User Story:** As an admin, I want to see all products (including inactive) when viewing a page while authenticated, so that I can preview and manage draft content.

**Acceptance Criteria:**
- `ShowPageController` currently hard-codes `->where('is_active', true)` in its eager-load of `categories.products` — this must become conditional on auth status
- Unauthenticated: keep filtering `is_active = true` (current behavior)
- Authenticated: remove the `is_active` filter, return all products

### REQ-6: Remove IndexPageController route

**Context:** The `GET /api/pages` index route (`IndexPageController`) is not consumed by the Nuxt frontend. It exposes all pages without purpose.

**Acceptance Criteria:**
- Remove the `Route::get('/', IndexPageController::class)->name('index')` line from `routes/api/pages.php`
- Delete `app/Http/Controllers/Page/IndexPageController.php`
- Remove or update any tests that reference the `api.pages.index` route

### REQ-7: Gallery index default ordering by `order` column

**User Story:** As a public visitor, I want galleries displayed in the order chosen by the admin, so that the portfolio presentation is intentional.

**Acceptance Criteria:**
- `GET /api/galleries` returns galleries sorted by `order` ascending (instead of the current `sortBy('id', desc)` in-memory sort)
- Ordering must happen at the query level (`ORDER BY order ASC`)
- The existing in-memory `sortBy('id', ...)` logic in `IndexGalleryController` is replaced by the query-level ordering

### REQ-8: Auto-increment gallery `order` on creation

**User Story:** As an admin, I want newly created galleries to automatically receive the next `order` value, so that I don't have to manually assign ordering for every gallery.

**Acceptance Criteria:**
- When `StoreGalleryController` creates a gallery without an explicit `order` value, it sets `order` to `MAX(order) + 1` from the galleries table (or `0` if no galleries exist)
- When an explicit `order` value is provided in the request, that value is used as-is
- Existing galleries all have `order = 0` — this is acceptable; new galleries will stack after them incrementally

## Non-Functional Requirements

### Architecture
- Add Eloquent local scopes (`scopeActive` on Product, `scopePublished` on Gallery) to encapsulate the filtering logic
- Use `auth('sanctum')->check()` (optional auth) in public controllers to detect auth status without requiring it — the routes must remain accessible without a token
- Do not add `auth:sanctum` middleware to the public GET routes; authentication must be optional
- Keep the single-action controller pattern — modify existing controllers, do not introduce middleware-based filtering or global scopes

### Performance
- Filtering must happen at the query level (`WHERE` clause), not in-memory after fetching all records
- Eager loading behavior unchanged — no additional queries introduced

### Security
- Unauthenticated users must never receive inactive/unpublished records in any response (index or show)
- The `is_active` / `is_published` fields should continue to be exposed in the resource response for authenticated users (useful for admin UI state)

### Reliability
- All existing tests must continue to pass
- New tests required for each requirement covering both authenticated and unauthenticated scenarios
- Test the 404 behavior on show routes for inactive/unpublished items without auth
