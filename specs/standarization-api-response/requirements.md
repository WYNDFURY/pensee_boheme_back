# Requirements — API Response Standardization

## 1. Introduction

The current API has inconsistent response shapes across endpoints. Some return resources wrapped in a `data` key (Laravel's JsonResource default), others return raw Eloquent models, and mutation endpoints wrap resources in custom named keys (`product`, `category`, etc.) with no `data` key. The Nuxt 3 frontend must handle multiple access patterns for the same type of data, adding complexity and fragility to data fetching logic.

This feature standardizes all API responses to a single, predictable shape: **flat everywhere, no `data` wrapper**.

## 2. Alignment with Product Vision

The site serves a Nuxt 3 SSG frontend that fetches content at build time. Predictable API contracts reduce frontend maintenance burden and the risk of runtime data-access bugs on a production static site. A consistent API also makes future developer handoff or frontend agent work significantly safer.

## 3. Current State

### Read endpoints — mixed wrapping

| Endpoint | Current shape | Problem |
|---|---|---|
| `GET /products` | `{ "data": [...] }` | `data` wrapper |
| `GET /products/{id}` | `{ "data": {...} }` | `data` wrapper |
| `GET /categories` | `[...]` | raw model, no Resource |
| `GET /categories/{id}` | `{...}` | raw model, no Resource |
| `GET /galleries` | `{ "data": [...] }` | `data` wrapper |
| `GET /galleries/{slug}` | `{ "data": {...} }` | `data` wrapper |
| `GET /pages` | `[...]` | raw model, no Resource |
| `GET /pages/{slug}` | `{ "data": {...} }` | `data` wrapper |
| `GET /instagram` | `{ "data": [...] }` | `data` wrapper |
| `GET /users` | `[...]` | raw model, no Resource |
| `GET /users/{id}` | `{...}` | raw model, no Resource |

### Mutation endpoints — inconsistent resource keys

| Endpoint | Current shape |
|---|---|
| `POST /products` | `{ "message": "...", "product": {...} }` |
| `POST /categories` | `{ "message": "...", "category": {...} }` |
| `POST /galleries` | `{ "message": "...", "gallery": {...} }` |
| `PATCH /categories/{id}` | `{ "message": "...", "category": {raw model} }` |
| `POST /users` | `{ raw user model }` |

## 4. Requirements

### US-1 — Flat read responses

**As a** frontend developer,
**I want** all read (`GET`) endpoints to return data without a `data` wrapper,
**so that** I can access response fields directly (e.g. `response.name`, `response[0].slug`).

**Acceptance criteria:**
- `JsonResource::withoutWrapping()` is called globally in `AppServiceProvider::boot()`
- All GET endpoints that currently return `{ "data": ... }` return the payload directly
- `GET /products`, `GET /products/{id}`, `GET /galleries`, `GET /galleries/{slug}`, `GET /pages/{slug}`, `GET /instagram` are affected
- Nested resources within a response (e.g. `categories` inside a page) are also flat (no nested `data` keys)

### US-2 — Resource classes for all read endpoints

**As a** frontend developer,
**I want** all GET endpoints to return data shaped by their Resource class,
**so that** the response contract is consistent and controlled (no raw Eloquent model leaking DB column names or unexpected fields).

**Acceptance criteria:**
- `GET /categories` uses `CategoryResource::collection()`
- `GET /categories/{id}` uses `new CategoryResource()`
- `GET /pages` uses `PageResource::collection()`
- `GET /users` and `GET /users/{id}` use `UserResource` (new Resource class to create)
- All controllers return their Resource directly, not via `response()->json(Resource)`

### US-3 — Consistent mutation response shape

**As a** frontend developer,
**I want** all mutation (`POST`, `PATCH`) responses to follow a single shape,
**so that** I can handle API responses with a single pattern.

**Acceptance criteria:**
- All store/update responses follow: `{ "message": "...", "data": {...} }` — a single `data` key regardless of resource type
- The `data` value is the serialized Resource (flat, no additional wrapping — US-1 ensures this)
- Example: `POST /products` → `{ "message": "Product created successfully", "data": { "id": 1, "name": "..." } }`
- `PATCH` responses follow the same shape
- `DELETE` responses remain `{ "message": "..." }` (no data)

### US-4 — Tests updated

**As a** developer,
**I want** all feature tests updated to match the new response shapes,
**so that** the test suite accurately reflects the API contract.

**Acceptance criteria:**
- All assertions on `data.field` (e.g. `assertJsonPath('data.id', ...)`) updated to `assertJsonPath('id', ...)`
- All assertions on named resource keys (e.g. `product.name`, `category.slug`) updated to `data.name`, `data.slug`
- All 143 existing tests pass after the changes

### US-5 — API reference updated

**As a** developer,
**I want** `docs/api-reference.md` updated to reflect the new response shapes,
**so that** the frontend agent works from accurate documentation.

**Acceptance criteria:**
- Response examples in `docs/api-reference.md` reflect the new flat shapes
- The `## Response Wrapping` section is updated to reflect the unified contract
- Resource schemas remain unchanged (only the wrapping changes, not field names)

## 5. Non-Functional Requirements

### Architecture
- Wrapping is disabled globally via `JsonResource::withoutWrapping()` — not per-controller. No controller should call `withoutWrapping()` individually.
- All controllers must go through a Resource class for responses — no raw `$model` serialization in `response()->json()`
- `UserResource` follows the same pattern as existing resources (flat array of model fields, no sensitive data like `password`)

### Performance
- No additional queries introduced. Resource classes must not trigger N+1 — use `whenLoaded()` for relationships.

### Reliability
- `php artisan test` must pass with zero failures after all changes
- The `data` key inside the `GalleryResource` `images_count` computed field logic must not be confused with the response wrapper — verify gallery tests specifically

### Usability
- The Nuxt frontend accesses read responses as: `const product = await $fetch('/api/products/1')` → `product.name` ✓
- Mutation responses accessed as: `const { data, message } = await $fetch('/api/products', { method: 'POST', ... })` → `data.name` ✓
