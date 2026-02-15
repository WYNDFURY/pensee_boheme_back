# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 10 REST API backend for **Pensée Bohème**, a brochure/portfolio website for a boutique eco-responsible florist in Normandy. The site showcases work, communicates values, and generates contact/booking inquiries. **Not an e-commerce store** — no cart, no payments. The API serves content (galleries, products, categories, pages) and processes contact form submissions. A Nuxt 3 frontend consumes this API at build time (SSG).

See `specs/product.md` for full product vision, target audiences, and brand identity.

### Business-to-Code Glossary

| Business Term | Model | Notes |
|---|---|---|
| Service line / "Univers" | `Page` | Top-level grouping (e.g. mariages, accessoires) |
| Creations | `Product` | Items within a Category, showcased with images |
| Options / Variants | `ProductOption` | Size, color, or style variants of a Product |
| Photo galleries | `Gallery` | Primary conversion tool — image-heavy, Spatie MediaLibrary |
| Contact requests | Contact form controllers | Two types: creation inquiries and event bookings |

## Commands

```bash
# Run all tests (Pest)
php artisan test

# Run a single test file
php artisan test --filter=StoreCategoryControllerTest

# Run tests in parallel
php artisan test --parallel

# Code formatting (Laravel Pint, PSR-12)
./vendor/bin/pint

# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Custom commands
php artisan app:fns-instagram-medias   # Fetch Instagram media
php artisan app:refresh-token          # Refresh Instagram token

# Regenerate media conversions for existing uploads
php artisan media-library:regenerate
php artisan media-library:regenerate --model=App\\Models\\Product
php artisan media-library:regenerate --model=App\\Models\\Gallery
```

## Architecture

### Single-Action Controllers

Each HTTP action is its own invokable controller class using `__invoke()`. Controllers live in feature-grouped directories:

```
app/Http/Controllers/
├── Product/
│   ├── StoreProductController.php
│   ├── IndexProductController.php
│   ├── ShowProductController.php
│   ├── UpdateProductController.php
│   └── DestroyProductController.php
├── Category/   (same pattern)
├── Gallery/    (same pattern)
├── User/       (same pattern)
├── Page/       (same pattern)
├── InstagramMedia/
├── CreationContactFormController.php
└── EventContactFormController.php
```

When creating new controllers, follow this one-class-per-action pattern.

### Route Organization

Routes are split into feature files under `routes/api/` and included from `routes/api.php`. All API routes use `throttle:60,1` middleware and `api.*` name prefix.

### Service Layer

Business logic for Instagram integration is extracted into services under `app/Services/` with clear separation (fetch, store, token refresh).

### API Resources

JSON response transformation uses `app/Http/Resources/*Resource.php` classes (Laravel API Resources).

### Key Models & Relationships

- **Product** → belongs to Category, has many ProductOptions, uses Spatie MediaLibrary, soft deletes
- **Category** → has many Products, belongs to Page, soft deletes
- **Page** → has many Categories
- **Gallery** → uses Spatie MediaLibrary
- **User** → Sanctum tokens, soft deletes

### External Integrations

- **Authentication**: Laravel Sanctum (token-based) — currently no auth middleware on API routes, admin login planned
- **Media/Images**: Spatie MediaLibrary with `spatie/image-optimizer`. Three responsive conversions per upload: `thumb` (400×400 crop, WebP q80), `medium` (1200px, WebP q85), `large` (2000px, WebP q85). All optimized and synchronous. `MediaResource` returns nested `urls` object with `thumb`, `medium`, `large`, `original`.
- **Email**: Mailgun (production), Mailpit (dev)
- **Instagram**: Meta Graph API v22.0, with retry logic (3 retries, 2s backoff)

### Contact Forms

Two contact form controllers with honeypot spam prevention (`additional_info` field).

## Testing

Uses **Pest PHP** with Laravel plugin. `RefreshDatabase` enabled globally in `tests/Pest.php` for both Feature and Unit directories. Always implement tests when building new features.

Tests run against a dedicated **`pensee_boheme_db_test`** MySQL database (configured in `phpunit.xml`), never the dev database. `RefreshDatabase` migrates and rolls back within this test DB.

- Feature tests: `tests/Feature/Http/Controllers/Api/`
- Unit tests: `tests/Unit/Models/`, `tests/Unit/Services/`, and `tests/Unit/Http/Resources/`
- Console tests: `tests/Feature/Console/`

## Skills

- **laravel-specialist** (`.claude/skills/laravel-specialist/`): Consult when implementing Laravel features — provides reference guides for Eloquent, routing/APIs, queues, Livewire, and testing under `references/`. Follow its constraints: type-hint all methods, use Eloquent relationships properly (avoid N+1), use API resources for response transformation, extract business logic into services, and write tests for every feature.
- **medialibrary-development** (`.claude/skills/medialibrary-development/`): Consult when working with file uploads, media attachments, or image processing. Covers Spatie MediaLibrary patterns: model setup (`HasMedia` + `InteractsWithMedia`), adding media from requests/URLs, defining collections and conversions, retrieving media URLs. See `references/medialibrary-guide.md` for detailed API.

## Spec-Driven Development

Complex features are documented before implementation. Spec documents go in `specs/<feature-slug>/` with a `requirements.md` file covering user stories, acceptance criteria, and non-functional requirements aligned with the product vision.
