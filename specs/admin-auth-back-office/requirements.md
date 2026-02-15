# Admin Authentication & Back Office — Requirements

## Introduction

Add authentication to the Pensée Bohème API so that content management endpoints (store, update, destroy) are protected behind admin login. Currently all routes are public. This feature introduces login/logout endpoints, applies `auth:sanctum` middleware to write operations, and ensures the admin can manage all site content securely.

Single admin user — no multi-user roles. This is a one-person artisan business.

## Alignment with Product Vision

From `specs/product.md`:

- **Site goal #1: Build trust & showcase quality** — read endpoints (index, show) must remain public so the Nuxt 3 SSG frontend can consume them at build time without credentials. Auth must not break the public site.
- **Site goal #5: Demonstrate professionalism** — a secure admin layer protects content integrity and prevents unauthorized modifications.
- **Technical constraint: Sanctum admin-only** — product.md explicitly states "admin-only, no public user accounts." No registration flow, no public sign-up.
- **Contact form submissions** — POST endpoints for contact forms must remain public (site visitors submit them without auth).

## Requirements

### R1 — Admin Login

**As an** admin, **I want** to authenticate with email and password, **so that** I receive a token to access protected endpoints.

Acceptance criteria:
- `POST /api/login` accepts `{email, password}` and returns a Sanctum plain-text token with user data
- Invalid credentials return 401 with a generic error message (no email/password enumeration)
- Validation errors (missing fields) return 422
- Login is rate-limited (5 attempts per minute per IP) to prevent brute force
- The returned token has no expiration (admin is always the same person, logout is explicit)

### R2 — Admin Logout

**As an** admin, **I want** to log out, **so that** my current token is revoked and can no longer be used.

Acceptance criteria:
- `POST /api/logout` requires `auth:sanctum` middleware
- Revokes the current access token only (not all tokens)
- Returns 200 with confirmation message
- Subsequent requests with the revoked token return 401

### R3 — Authenticated User Endpoint

**As an** admin, **I want** to retrieve my profile from a protected endpoint, **so that** the back office can verify the session is valid and display my info.

Acceptance criteria:
- `GET /api/user` requires `auth:sanctum` (already exists, keep as-is)
- Returns the authenticated user's `id`, `first_name`, `last_name`, `email`
- Unauthenticated requests return 401 JSON (not redirect)

### R4 — Protect Write Endpoints

**As an** admin, **I want** store, update, and destroy endpoints to require authentication, **so that** only I can modify site content.

Acceptance criteria:
- The following routes require `auth:sanctum` middleware:
  - `POST`, `PATCH`, `DELETE` on `/api/products/*`
  - `POST`, `PATCH`, `DELETE` on `/api/categories/*`
  - `POST`, `PATCH`, `DELETE` on `/api/pages/*`
  - `POST`, `PATCH`, `DELETE` on `/api/galleries/*`
  - `POST`, `PATCH`, `DELETE` on `/api/users/*`
- The following routes remain **public** (no auth):
  - `GET` on `/api/products`, `/api/categories`, `/api/pages`, `/api/galleries`, `/api/users`, `/api/instagram`
  - `POST` on `/api/contact/creation`, `/api/contact/event`
- Unauthenticated requests to protected routes return 401 JSON `{"message": "Unauthenticated."}`

### R5 — Auth Route File

**As a** developer, **I want** auth routes organized in a dedicated route file, **so that** they follow the existing `routes/api/` convention.

Acceptance criteria:
- Auth routes live in `routes/api/auth.php`
- Included from `routes/api.php` like other route files
- Login and logout use single-action controllers (`LoginController`, `LogoutController`) under `app/Http/Controllers/Auth/`

### R6 — Kernel & Middleware Configuration

**As a** developer, **I want** Sanctum properly configured for API token auth, **so that** the `auth:sanctum` middleware works correctly.

Acceptance criteria:
- `EnsureFrontendRequestsAreStateful` remains commented out in `Kernel.php` (token-based auth, not cookie/session)
- `Authenticate` middleware returns 401 JSON for all API requests (never redirects to a `login` route)
- Existing throttle middleware (`throttle:60,1`) continues to apply to all API routes

### R7 — Admin Seeder

**As a** developer, **I want** a seeder that creates the admin user, **so that** the admin account exists after `migrate:fresh --seed`.

Acceptance criteria:
- `database/seeders/AdminUserSeeder.php` creates a single admin user with credentials from `.env` (`ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_FIRST_NAME`, `ADMIN_LAST_NAME`)
- Seeder is idempotent — does not duplicate if run multiple times (uses `firstOrCreate`)
- `DatabaseSeeder.php` calls `AdminUserSeeder`
- Default `.env.example` includes the admin env vars with placeholder values

## Non-Functional Requirements

### Architecture
- Follow existing single-action controller pattern (`LoginController`, `LogoutController` with `__invoke()`)
- Auth controllers go in `app/Http/Controllers/Auth/` directory
- No new service classes needed — login logic is simple enough for controller-level code
- Route file pattern: `routes/api/auth.php`

### Security
- Passwords hashed with `bcrypt` (already the case via `Hash::make`)
- Login rate-limited to 5 attempts/minute per IP (use Laravel `ThrottleRequests` or `RateLimiter`)
- Generic "Invalid credentials" error on login failure — do not reveal whether email or password was wrong
- Tokens stored hashed in `personal_access_tokens` table (Sanctum default)
- No token in URL query strings — always via `Authorization: Bearer` header

### Performance
- Login/logout are lightweight operations — no performance concerns
- Adding `auth:sanctum` middleware to write routes adds negligible overhead (single DB lookup on `personal_access_tokens`)

### Reliability
- Existing test suite must continue to pass — feature tests that call write endpoints will need to use `actingAs()` or `Sanctum::actingAs()` to authenticate
- New tests required for login, logout, and auth middleware behavior on all protected endpoints

### Usability
- Token returned on login is the only credential needed — simple `Authorization: Bearer {token}` header for all subsequent requests
- Clear error messages: 401 for unauthenticated, 422 for validation errors
