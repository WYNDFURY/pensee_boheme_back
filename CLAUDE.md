# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 10 REST API backend for "Pensée Bohème", an e-commerce/portfolio platform. PHP 8.1+, MySQL, runs on Laragon (Windows).

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

- **Authentication**: Laravel Sanctum (token-based)
- **Media/Images**: Spatie MediaLibrary (WebP conversion, 50MB max)
- **Email**: Mailgun (production), Mailpit (dev)
- **Instagram**: Meta Graph API v22.0, with retry logic (3 retries, 2s backoff)

### Contact Forms

Two contact form controllers with honeypot spam prevention (`additional_info` field).

## Testing

Uses **Pest PHP** with Laravel plugin. Tests in `tests/Feature/Http/Controllers/Api/`. RefreshDatabase trait is available but commented out in `tests/Pest.php` — enable per-test as needed.

## Spec-Driven Development

Complex features are documented before implementation. Spec documents go in `specs/<feature-slug>/` with a `requirements.md` file covering user stories, acceptance criteria, and non-functional requirements aligned with the product vision.
