# Pensee Boheme — API Reference

**Base URL:** `/api`
**Auth:** Laravel Sanctum Bearer token (obtain via `/login`)
**Throttle:** 60 requests/minute

---

## Authentication

### POST `/login`

**Body:**
```json
{ "email": "string", "password": "string" }
```

**Response (200):**
```json
{
  "token": "plaintext-sanctum-token",
  "user": { "id": 1, "first_name": "string", "last_name": "string", "email": "string" }
}
```

### POST `/logout` `auth:sanctum`

**Response (200):** `{ "message": "Logged out" }`

---

## Products

### GET `/products`

Returns products. No auth required.

**Visibility filtering:**
- **Unauthenticated:** only products with `is_active = true`
- **Authenticated (Bearer token):** all products including inactive

**Response:** Array of ProductResource (no `data` wrapper)

### GET `/products/{id}`

Single product with media, options, and category.

**Visibility filtering:**
- **Unauthenticated:** returns 404 if `is_active = false`
- **Authenticated (Bearer token):** returns any product regardless of `is_active`

**Response:** Single ProductResource

### POST `/products` `auth:sanctum`

**Body (multipart/form-data):**

| Field | Type | Rules |
|-------|------|-------|
| name | string | required, max:255 |
| slug | string | required, max:255, unique |
| description | string | nullable |
| price | numeric | nullable, min:0 |
| has_price | boolean | optional |
| is_active | boolean | optional |
| category_id | int | required, exists:categories |
| image | file | nullable, image, mimes:jpeg,png,webp,gif, max:10MB |

**Response (201):**
```json
{
  "message": "Product created successfully",
  "data": { ProductResource }
}
```

### PATCH `/products/{id}` `auth:sanctum`

Same fields as POST (all optional). Use `POST` with `_method=PATCH` for file uploads.

**Response (200):**
```json
{
  "message": "Product updated successfully",
  "data": { ProductResource }
}
```

### DELETE `/products/{id}` `auth:sanctum`

Soft deletes product. **Response (200):** `{ "message": "Product deleted" }`

---

## Categories

### GET `/categories`

Returns all categories. No `data` wrapper.

### GET `/categories/{id}`

Single category with nested products (CategoryResource).

### POST `/categories` `auth:sanctum`

| Field | Type | Rules |
|-------|------|-------|
| name | string | required, max:255 |
| slug | string | required, max:255, unique |
| description | string | nullable |
| order | int | nullable |
| page_id | int | required, exists:pages |

**Response (201):** `{ "message": "Category created successfully", "data": { CategoryResource } }`

### PATCH `/categories/{id}` `auth:sanctum`

**Response (200):** `{ "message": "Category updated successfully", "data": { CategoryResource } }`

### DELETE `/categories/{id}` `auth:sanctum`

---

## Pages

### GET `/pages/{slug}`

Single page with nested categories and products (PageResource).

**Visibility filtering on nested products:**
- **Unauthenticated:** only products with `is_active = true` within each category
- **Authenticated (Bearer token):** all products including inactive

### POST `/pages` `auth:sanctum`

| Field | Type | Rules |
|-------|------|-------|
| slug | string | required, max:255, unique |

**Response (201):** `{ "message": "Page created successfully", "data": { PageResource } }`

### PATCH `/pages/{slug}` `auth:sanctum`

**Response (200):** `{ "message": "Page updated successfully", "data": { PageResource } }`

### DELETE `/pages/{slug}` `auth:sanctum`

---

## Galleries

### GET `/galleries`

Returns galleries sorted by `order` ascending. No `data` wrapper.

**Visibility filtering:**
- **Unauthenticated:** only galleries with `is_published = true` and at least one media item
- **Authenticated (Bearer token):** all galleries including unpublished and empty

**Special behavior:**
- `media` limited to **3 items** per gallery (preview)
- `images_count` shows **total** count

### GET `/galleries/{slug}`

Single gallery with **all media** items.

**Visibility filtering:**
- **Unauthenticated:** returns 404 if `is_published = false`
- **Authenticated (Bearer token):** returns any gallery regardless of `is_published`

### POST `/galleries` `auth:sanctum`

**Body (multipart/form-data):**

| Field | Type | Rules |
|-------|------|-------|
| name | string | required, max:255 |
| slug | string | required, max:255, unique |
| description | string | nullable |
| photographer | string | nullable |
| is_published | boolean | optional |
| order | int | optional, auto-increments if omitted |
| images[] | file[] | nullable, max 20, each: image, mimes:jpeg,png,webp,gif, max:10MB |

**Response (201):**
```json
{
  "message": "Gallery created successfully",
  "data": { GalleryResource }
}
```

### PATCH `/galleries/{slug}` `auth:sanctum`

Use `POST` with `_method=PATCH` for file uploads. New images are **appended** (existing preserved).

### DELETE `/galleries/{slug}` `auth:sanctum`

---

## Users

### GET `/users` `auth:sanctum`

Returns all users (no `data` wrapper).

### GET `/users/{id}` `auth:sanctum`

Single user (UserResource).

### POST `/users` `auth:sanctum`

| Field | Type | Rules |
|-------|------|-------|
| first_name | string | required, max:255 |
| last_name | string | required, max:255 |
| email | string | required, email, unique |
| password | string | required, min:8 |

**Response (201):** `{ "message": "User created successfully", "data": { UserResource } }`

### PATCH `/users/{id}` `auth:sanctum`

Same fields as POST (all optional).

**Response (200):** `{ "message": "User updated successfully", "data": { UserResource } }`

### DELETE `/users/{id}` `auth:sanctum`

Soft deletes user. **Response (200):** `{ "message": "User deleted" }`

---

## Media

### DELETE `/media/{id}` `auth:sanctum`

Deletes a specific media attachment (image). **Response (200):** `{ "message": "Media deleted" }`

---

## Instagram

### GET `/instagram`

Returns last 12 Instagram media items sorted by timestamp (desc). No auth required.

---

## Contact Forms

### POST `/contact/creation`

| Field | Type | Rules |
|-------|------|-------|
| firstName | string | required, max:255 |
| lastName | string | required, max:255 |
| email | string | required, email |
| phone | string | required, max:20 |
| message | string | required, max:1000 |
| additional_info | string | nullable (honeypot — must be empty) |

### POST `/contact/event`

| Field | Type | Rules |
|-------|------|-------|
| firstName | string | required, max:255 |
| lastName | string | required, max:255 |
| email | string | required, email |
| phone | string | required, max:20 |
| eventDate | date | required |
| eventLocation | string | required, max:255 |
| themeColors | string | required, max:255 |
| message | string | required, max:1000 |
| additional_info | string | nullable (honeypot — must be empty) |

**Success (200):** `{ "message": "..." }`
**Spam (422):** `{ "message": "Spam detected" }`
**Mail error (500):** `{ "message": "Erreur lors de l'envoi du formulaire" }`

---

## Resource Schemas

### ProductResource

```typescript
{
  id: number
  name: string
  slug: string
  description: string | null
  price: number | null
  price_formatted: string | null  // e.g. "25.00 €" (null if has_price is false)
  is_active: boolean
  has_price: boolean
  category_name: string | null
  media: MediaResource[]
  options?: ProductOptionResource[]  // only if loaded and non-empty
}
```

### ProductOptionResource

```typescript
{
  id: number
  name: string
  price: number | null
  price_formatted: string | null  // e.g. "10.00€"
}
```

### CategoryResource

```typescript
{
  id: number
  name: string
  slug: string
  description: string | null
  order: number
  page_slug: string | null
  products?: ProductResource[]  // when loaded
}
```

### PageResource

```typescript
{
  id: number
  slug: string
  categories: CategoryResource[]  // with nested products
}
```

### GalleryResource

```typescript
{
  id: number
  name: string
  photographer: string | null
  slug: string
  description: string | null
  is_published: boolean
  cover_image: string | null
  order: number
  images_count: number           // total image count (always full count)
  media: MediaResource[]         // 3 items on index, all on show
}
```

### MediaResource

```typescript
{
  id: number
  name: string
  file_name: string
  mime_type: string
  size: number                   // bytes
  urls: {
    thumb: string                // 400x400 center-crop WebP
    medium: string               // 1200px max-width WebP
    large: string                // 2000px max-width WebP
    original: string             // original uploaded file
  }
}
```

### UserResource

```typescript
{
  id: number
  first_name: string
  last_name: string
  email: string
}
```

### InstagramMediaResource

```typescript
{
  id: number
  media_type: "IMAGE" | "VIDEO" | "CAROUSEL_ALBUM"
  media_url: string
  permalink: string
  caption: string | null
  timestamp: string              // ISO datetime
}
```

---

## Response Wrapping

All responses are flat — no `data` wrapper on GET endpoints. `JsonResource::withoutWrapping()` is enabled globally.

| Endpoint type | Shape |
|---------------|-------|
| GET list (all resources) | `[...]` |
| GET single (all resources) | `{...}` |
| POST / PATCH | `{ "message": "...", "data": {...} }` |
| DELETE | `{ "message": "..." }` |

---

## Error Responses

**Validation (422):**
```json
{ "message": "Validation failed", "errors": { "field": ["error message"] } }
```

**Unauthenticated (401):**
```json
{ "message": "Unauthenticated." }
```

**Not Found (404):**
```json
{ "message": "No query results for model [...]" }
```
