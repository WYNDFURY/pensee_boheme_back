# Roadmap — Pensée Bohème Backend

Planned features in priority order. Each feature will get its own `specs/<feature-slug>/` directory with requirements, design, and implementation documents when work begins.

---

## 1. Admin Authentication & Back Office

**Priority:** Next up
**Status:** Not started

### Context
Currently no auth middleware protects API routes. The User model already supports Sanctum tokens, but there's no login/logout flow, no session management, and no admin-guarded routes. The back office will be the admin interface for managing all site content.

### Scope
- **Login endpoint** — email/password authentication returning a Sanctum token
- **Logout endpoint** — revoke current token
- **Auth middleware** — protect all write endpoints (store, update, destroy) behind `auth:sanctum`
- **Back office routes** — admin-only endpoints for managing:
  - Products, Categories, Pages (CRUD already exists, needs auth guard)
  - Galleries (CRUD + media upload)
  - Instagram settings (token management)
  - Contact form submissions (view/manage inquiries)
- **Session/token management** — token expiration, refresh strategy

### Key Decisions to Make
- Single admin user or multi-user with roles?
- Token expiration policy (long-lived vs. short-lived with refresh)
- Should read endpoints (index, show) remain public or also be guarded?
- Back office as separate route group or same API with middleware?

---

## 2. Media Optimization

**Priority:** High (directly impacts gallery performance — the primary conversion tool)
**Status:** Not started

### Context
Spatie MediaLibrary is already in use for Products and Galleries with basic WebP conversion. Current implementation uses a single `optimized` conversion at quality 90. Galleries are image-heavy and are the primary conversion tool for the business — performance here directly impacts user experience and SEO.

### Scope
- **Responsive image sizes** — generate multiple conversions (thumbnail, medium, large, full) instead of just one
- **Smarter compression** — adaptive quality based on image content, target file sizes
- **Upload validation** — stricter dimension/size limits per context (gallery vs. product)
- **Lazy loading support** — provide low-quality placeholder (LQIP) or blur hash for frontend
- **Storage cleanup** — handle orphaned media, conversion failures

### Key Decisions to Make
- Which conversion sizes? (e.g., 400px thumb, 800px medium, 1600px large, original)
- Target quality per size? (thumb can be lower quality than full)
- Generate blur hashes server-side or let frontend handle it?
- Should conversions be queued or synchronous?

---

## 3. E-Commerce (Future)

**Priority:** Later — not in current scope
**Status:** Planning only

### Context
The site is currently brochure/portfolio only. A future phase may add e-commerce capabilities for selling dried flower accessories, guest gifts, and workshop bookings directly.

### Potential Scope
- Shopping cart and checkout flow
- Payment processing (Stripe or similar)
- Order management
- Stock/inventory tracking for ProductOptions
- Booking system for workshops (date/capacity management)
- Customer accounts (extends current User model)

### Dependencies
- Requires admin auth & back office (feature 1) to be complete first
- Requires media optimization (feature 2) for product image performance
