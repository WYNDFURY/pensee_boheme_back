# Backend Test Suite — Design Document

## Overview

Replace the existing incomplete test suite with a comprehensive Pest PHP test suite covering all 26 API endpoints, 6 services, 2 console commands, and 8 models. Tests are organized by layer (Feature for HTTP, Unit for models/services) and use factories, `RefreshDatabase`, `Mail::fake()`, and `Http::fake()` for isolation.

## Architecture

```
tests/
├── Pest.php                          # Config: RefreshDatabase, helpers
├── TestCase.php                      # Base (unchanged)
├── CreatesApplication.php            # Bootstrap (unchanged)
├── Feature/
│   ├── Http/Controllers/Api/
│   │   ├── User/
│   │   │   ├── IndexUserControllerTest.php
│   │   │   ├── StoreUserControllerTest.php
│   │   │   ├── ShowUserControllerTest.php
│   │   │   ├── UpdateUserControllerTest.php
│   │   │   └── DestroyUserControllerTest.php
│   │   ├── Page/
│   │   │   ├── IndexPageControllerTest.php
│   │   │   ├── StorePageControllerTest.php
│   │   │   ├── ShowPageControllerTest.php
│   │   │   ├── UpdatePageControllerTest.php
│   │   │   └── DestroyPageControllerTest.php
│   │   ├── Category/
│   │   │   ├── IndexCategoryControllerTest.php
│   │   │   ├── StoreCategoryControllerTest.php
│   │   │   ├── ShowCategoryControllerTest.php
│   │   │   ├── UpdateCategoryControllerTest.php
│   │   │   └── DestroyCategoryControllerTest.php
│   │   ├── Product/
│   │   │   ├── IndexProductControllerTest.php
│   │   │   ├── StoreProductControllerTest.php
│   │   │   ├── ShowProductControllerTest.php
│   │   │   ├── UpdateProductControllerTest.php
│   │   │   └── DestroyProductControllerTest.php
│   │   ├── Gallery/
│   │   │   ├── IndexGalleryControllerTest.php
│   │   │   ├── StoreGalleryControllerTest.php
│   │   │   ├── ShowGalleryControllerTest.php
│   │   │   ├── UpdateGalleryControllerTest.php
│   │   │   └── DestroyGalleryControllerTest.php
│   │   ├── InstagramMedia/
│   │   │   └── IndexInstagramMediaControllerTest.php
│   │   └── Contact/
│   │       ├── CreationContactFormControllerTest.php
│   │       └── EventContactFormControllerTest.php
│   └── Console/
│       ├── FetchAndStoreInstagramMediasTest.php
│       └── RefreshTokenTest.php
└── Unit/
    ├── Models/
    │   ├── UserTest.php
    │   ├── PageTest.php
    │   ├── CategoryTest.php
    │   ├── ProductTest.php
    │   ├── ProductOptionTest.php
    │   ├── GalleryTest.php
    │   ├── InstagramMediaTest.php
    │   └── InstagramAccessTokenTest.php
    └── Services/
        ├── FetchInstagramMediaServiceTest.php
        ├── StoreInstagramMediaServiceTest.php
        ├── StartStoringInstagramMediaServiceTest.php
        ├── RefreshLongLivedTokenServiceTest.php
        ├── UpdatesLongLivedTokenServiceTest.php
        └── StartRefreshingLongLivedTokenServiceTest.php
```

**Files to delete:**
- `tests/Feature/BasicTest.php`
- `tests/Feature/mail/MailTest.php`

**Files to rewrite** (in existing paths under `tests/Feature/Http/Controllers/Api/`):
- All 5 Product tests
- All 5 Category tests (folder rename: `Categories/` → `Category/`)
- All 5 User tests (folder rename: `Users/` → `User/`)

## Components and Interfaces

### C1 — tests/Pest.php (modified)

```php
<?php

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Unit');
```

No helper functions needed — each test file imports its own dependencies via `use function Pest\Laravel\*`.

### C2 — CRUD Feature Test Pattern

Every CRUD resource follows the same test structure. Below is the canonical pattern using **Category** as the example (since it has the most interesting validation: requires `page_id` FK).

Each test file contains one `it()` block per scenario. Uses Pest Laravel helpers (`get`, `post`, `patch`, `delete` from `Pest\Laravel`).

#### StoreCategoryControllerTest.php — Pattern

```php
<?php

use function Pest\Laravel\post;
use App\Models\Page;

it('creates a category with valid data', function () {
    $page = Page::factory()->create();

    $response = post('/api/categories', [
        'name' => 'Accessoires',
        'description' => 'Floral accessories',
        'order' => 1,
        'page_id' => $page->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('category.name', 'Accessoires')
        ->assertJsonPath('category.page_id', $page->id);

    $this->assertDatabaseHas('categories', [
        'name' => 'Accessoires',
        'page_id' => $page->id,
    ]);
});

it('rejects missing required fields', function () {
    post('/api/categories', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'page_id']);
});

it('rejects nonexistent page_id', function () {
    post('/api/categories', [
        'name' => 'Test',
        'page_id' => 9999,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['page_id']);
});
```

#### Index/Show/Update/Destroy — Pattern

```php
// Index
it('returns all categories', function () {
    Category::factory()->count(3)->create();
    get('/api/categories')->assertOk()->assertJsonCount(3);
});

it('returns empty array when none exist', function () {
    get('/api/categories')->assertOk()->assertJsonCount(0);
});

// Show
it('returns a category', function () {
    $category = Category::factory()->create();
    get("/api/categories/{$category->id}")
        ->assertOk()
        ->assertJsonPath('id', $category->id)
        ->assertJsonPath('name', $category->name);
});

it('returns 404 for nonexistent category', function () {
    get('/api/categories/9999')->assertNotFound();
});

// Update
it('updates a category', function () {
    $category = Category::factory()->create();
    patch("/api/categories/{$category->id}", ['name' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('category.name', 'Updated');
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated']);
});

it('allows partial update', function () {
    $category = Category::factory()->create();
    $originalName = $category->name;
    patch("/api/categories/{$category->id}", ['description' => 'New desc'])
        ->assertOk();
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $originalName]);
});

// Destroy
it('soft deletes a category', function () {
    $category = Category::factory()->create();
    delete("/api/categories/{$category->id}")->assertOk();
    $this->assertSoftDeleted($category);
});

it('returns 404 when deleting nonexistent category', function () {
    delete('/api/categories/9999')->assertNotFound();
});
```

### C3 — Slug-Bound Resources (Page, Gallery)

Pages and Galleries use `{page:slug}` and `{gallery:slug}` route binding. Tests must use the slug in URLs:

```php
// Show
get("/api/pages/{$page->slug}")->assertOk();

// Update
patch("/api/pages/{$page->slug}", ['slug' => 'new-slug'])->assertOk();

// Destroy
delete("/api/pages/{$page->slug}")->assertOk();

// 404
get('/api/pages/nonexistent-slug')->assertNotFound();
```

### C4 — Page Show: Nested Relationships

`ShowPageController` eager-loads categories → products → media. Test verifies the structure:

```php
it('returns page with nested categories and active products', function () {
    $page = Page::factory()->create();
    $category = Category::factory()->create(['page_id' => $page->id, 'order' => 1]);
    $activeProduct = Product::factory()->create([
        'category_id' => $category->id,
        'is_active' => true,
    ]);
    $inactiveProduct = Product::factory()->create([
        'category_id' => $category->id,
        'is_active' => false,
    ]);

    $response = get("/api/pages/{$page->slug}");

    $response->assertOk()
        ->assertJsonPath('data.slug', $page->slug)
        ->assertJsonCount(1, 'data.categories')
        ->assertJsonCount(1, 'data.categories.0.products'); // only active product
});
```

**Note:** `PageResource` wraps response in `data` key (JsonResource convention). Raw `response()->json()` controllers do not.

### C5 — Product CRUD: Response Structure

`IndexProductController` and `ShowProductController` use `ProductResource`, so responses are wrapped in `data` key. `StoreProductController` and `UpdateProductController` return raw JSON with `product` key.

Test assertions must match the actual response shape:
- Index: `assertJsonCount(N, 'data')` (if resource collection wraps in `data`) or `assertJsonCount(N)` (if manual `response()->json()`)
- Store: `assertJsonPath('product.name', ...)`
- Show: `assertJsonPath('data.name', ...)` (JsonResource wraps in `data`)

**Important:** The current `IndexProductController` does `response()->json($products)` after wrapping in `ProductResource::collection()`. When a resource collection is passed to `response()->json()`, the result is a flat array (no `data` wrapper). This is different from returning the resource directly. Tests must verify the actual output shape.

### C6 — Product Store: Missing `slug` Validation

The current `StoreProductControllerTest` does NOT send `slug`, which is `required|unique:products`. The existing test passes because the factory creates the product separately. The rewritten test must include `slug`:

```php
it('creates a product with valid data', function () {
    $category = Category::factory()->create();

    $response = post('/api/products', [
        'name' => 'Peigne Fleur',
        'slug' => 'peigne-fleur',
        'description' => 'Beautiful hair comb',
        'price' => 25.00,
        'category_id' => $category->id,
        'is_active' => true,
        'has_price' => true,
    ]);

    $response->assertCreated()
        ->assertJsonPath('product.name', 'Peigne Fleur')
        ->assertJsonPath('product.slug', 'peigne-fleur');

    $this->assertDatabaseHas('products', ['slug' => 'peigne-fleur']);
});

it('rejects missing required fields', function () {
    post('/api/products', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'slug', 'category_id']);
});

it('rejects duplicate slug', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['slug' => 'existing-slug']);

    post('/api/products', [
        'name' => 'Another',
        'slug' => 'existing-slug',
        'category_id' => $category->id,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

it('rejects negative price', function () {
    $category = Category::factory()->create();

    post('/api/products', [
        'name' => 'Test',
        'slug' => 'test',
        'category_id' => $category->id,
        'price' => -5,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['price']);
});
```

### C7 — DestroyProductController Bug

`DestroyProductController` has a `dd($medias)` on line 12 that halts execution before `$product->delete()`. This must be fixed before tests will pass:

```php
// Current (broken):
$medias = $product->getMedia('*');
dd($medias);              // <-- REMOVE THIS
$product->delete();

// Fixed:
$product->delete();
```

The destroy test will fail until this is fixed. The design assumes it will be fixed as part of implementation.

### C8 — User CRUD: Password Hashing Verification

```php
it('hashes password on creation', function () {
    post('/api/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'secret123',
    ])->assertCreated();

    $user = User::where('email', 'john@example.com')->first();
    expect(Hash::check('secret123', $user->password))->toBeTrue();
});

it('rejects password under 8 characters', function () {
    post('/api/users', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'short',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    post('/api/users', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'taken@example.com',
        'password' => 'password123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
```

### C9 — Gallery Index: Filters Galleries Without Media

`IndexGalleryController` filters out galleries that have no `gallery_images` media. Since we cannot easily add real media in tests (requires actual files + Spatie), the index test should verify that galleries without media are excluded:

```php
it('excludes galleries without media', function () {
    Gallery::factory()->count(3)->create(); // no media attached

    get('/api/galleries')->assertOk()->assertJsonCount(0);
});
```

### C10 — Contact Form Tests

```php
// CreationContactFormControllerTest.php
use App\Mail\CreationContactFormMail;
use Illuminate\Support\Facades\Mail;

it('sends creation contact email with valid data', function () {
    Mail::fake();

    $response = $this->postJson('/api/contact/creation', [
        'firstName' => 'Marie',
        'lastName' => 'Dupont',
        'email' => 'marie@example.com',
        'phone' => '+33612345678',
        'message' => 'Je souhaite une création florale.',
        'additional_info' => '',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Formulaire envoyé avec succès']);

    Mail::assertSent(CreationContactFormMail::class, function ($mail) {
        return $mail->contactData['email'] === 'marie@example.com';
    });
});

it('rejects missing required fields', function () {
    Mail::fake();

    $this->postJson('/api/contact/creation', [])
        ->assertUnprocessable()
        ->assertJson(['message' => 'Validation failed'])
        ->assertJsonValidationErrors(['firstName', 'lastName', 'email', 'phone', 'message']);

    Mail::assertNothingSent();
});

it('rejects spam via honeypot', function () {
    Mail::fake();

    $this->postJson('/api/contact/creation', [
        'firstName' => 'Bot',
        'lastName' => 'Spam',
        'email' => 'bot@spam.com',
        'phone' => '+33600000000',
        'message' => 'Spam content',
        'additional_info' => 'filled-honeypot',
    ])->assertUnprocessable()
        ->assertJson(['message' => 'Spam detected']);

    Mail::assertNothingSent();
});

it('returns 500 when mail sending fails', function () {
    Mail::fake();
    Mail::shouldReceive('to->send')->andThrow(new \Exception('Mail error'));

    $this->postJson('/api/contact/creation', [
        'firstName' => 'Marie',
        'lastName' => 'Dupont',
        'email' => 'marie@example.com',
        'phone' => '+33612345678',
        'message' => 'Test message',
        'additional_info' => '',
    ])->assertStatus(500)
        ->assertJson(['message' => "Erreur lors de l'envoi du formulaire"]);
});
```

Same pattern for `EventContactFormControllerTest.php` with additional fields (`eventDate`, `eventLocation`, `themeColors`).

### C11 — Instagram Media Endpoint Test

```php
use App\Models\InstagramMedia;

it('returns at most 12 instagram media sorted by timestamp desc', function () {
    // Create 15 records with different timestamps
    for ($i = 1; $i <= 15; $i++) {
        InstagramMedia::create([
            'media_id' => "media_{$i}",
            'caption' => "Caption {$i}",
            'media_type' => 'IMAGE',
            'media_url' => "https://example.com/img_{$i}.jpg",
            'permalink' => "https://instagram.com/p/{$i}",
            'timestamp' => now()->subDays(15 - $i), // media_15 = newest
        ]);
    }

    $response = get('/api/instagram');

    $response->assertOk();
    $data = $response->json();
    expect(count($data))->toBeLessThanOrEqual(12);

    // Verify descending order
    $timestamps = collect($data)->pluck('timestamp');
    expect($timestamps->toArray())->toBe($timestamps->sortDesc()->values()->toArray());
});

it('returns empty array when no media exists', function () {
    get('/api/instagram')->assertOk()->assertJsonCount(0);
});
```

### C12 — Instagram Service Unit Tests

Services have hard dependencies in constructors (read from DB, decrypt tokens). Tests must either mock the dependencies or seed the required data.

**Strategy for FetchInstagramMediaService and RefreshLongLivedTokenService:**
These services call `InstagramAccessToken::where('id', 1)->first()` and `decrypt()` in the constructor. To test them:

1. Create an `InstagramAccessToken` factory (missing — must be created)
2. Seed an encrypted token before each test
3. Use `Http::fake()` to mock external API calls

```php
// Factory to create (new file: database/factories/InstagramAccessTokenFactory.php)
// definition: ['access_token' => encrypt('fake-token'), 'expires_at' => now()->addMonths(3)]
```

```php
// FetchInstagramMediaServiceTest.php
use App\Services\StoreInstagramMedias\FetchInstagramMediaService;
use App\Models\InstagramAccessToken;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    InstagramAccessToken::create([
        'id' => 1,
        'access_token' => encrypt('fake-token-value'),
        'expires_at' => now()->addMonths(3),
    ]);

    config([
        'tokenHandler.meta_app_id' => 'fake-app-id',
        'tokenHandler.meta_app_secret' => 'fake-app-secret',
        'tokenHandler.instagram_account_id' => 'fake-account-id',
    ]);
});

it('fetches and filters instagram media', function () {
    Http::fake([
        'graph.facebook.com/*' => Http::response([
            'data' => [
                ['id' => '1', 'caption' => 'A', 'media_type' => 'IMAGE', 'media_url' => 'http://img/1', 'permalink' => 'http://p/1', 'timestamp' => '2024-01-01'],
                ['id' => '2', 'caption' => 'B', 'media_type' => 'VIDEO', 'media_url' => 'http://img/2', 'permalink' => 'http://p/2', 'timestamp' => '2024-01-02'],
                ['id' => '3', 'caption' => 'C', 'media_type' => 'CAROUSEL_ALBUM', 'media_url' => 'http://img/3', 'permalink' => 'http://p/3', 'timestamp' => '2024-01-03'],
            ],
        ]),
    ]);

    $service = app(FetchInstagramMediaService::class);
    $result = $service->fetchInstagramMedias();

    expect($result)->toHaveCount(2); // IMAGE + CAROUSEL_ALBUM, not VIDEO
    expect(collect($result)->pluck('media_type')->toArray())
        ->each->toBeIn(['IMAGE', 'CAROUSEL_ALBUM']);
});
```

**Strategy for StoreInstagramMediaService:**

```php
it('creates instagram media records from data', function () {
    $service = new StoreInstagramMediaService();
    $data = [
        ['id' => '1', 'caption' => 'A', 'media_type' => 'IMAGE', 'media_url' => 'url1', 'permalink' => 'p1', 'timestamp' => '2024-01-01'],
        ['id' => '2', 'caption' => 'B', 'media_type' => 'IMAGE', 'media_url' => 'url2', 'permalink' => 'p2', 'timestamp' => '2024-01-02'],
    ];

    $service->storeInstagramMedia($data);

    $this->assertDatabaseCount('instagram_media', 2);
    $this->assertDatabaseHas('instagram_media', ['media_id' => '1']);
});
```

**Strategy for StartStoringInstagramMediaService:**

```php
it('clears existing media and stores new batch', function () {
    // Pre-populate
    InstagramMedia::create([...]);

    Http::fake([...]);

    $service = app(StartStoringInstagramMediaService::class);
    $service->startStoringInstagramMedia();

    // Old records cleared, new ones stored
    $this->assertDatabaseCount('instagram_media', /* expected count from fake */);
});
```

### C13 — Console Command Tests

```php
// FetchAndStoreInstagramMediasTest.php
use App\Services\StoreInstagramMedias\StartStoringInstagramMediaService;

it('runs fns-instagram-medias command', function () {
    $mock = Mockery::mock(StartStoringInstagramMediaService::class);
    $mock->shouldReceive('startStoringInstagramMedia')->once();
    $this->app->instance(StartStoringInstagramMediaService::class, $mock);

    $this->artisan('app:fns-instagram-medias')
        ->expectsOutput('Fetching and storing Instagram media...')
        ->expectsOutput('Instagram media fetched and stored successfully.')
        ->assertExitCode(0);
});
```

### C14 — Model Unit Tests

```php
// tests/Unit/Models/CategoryTest.php
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;

it('belongs to a page', function () {
    $category = Category::factory()->create();
    expect($category->page)->toBeInstanceOf(Page::class);
});

it('has many products', function () {
    $category = Category::factory()->create();
    Product::factory()->count(3)->create(['category_id' => $category->id]);
    expect($category->products)->toHaveCount(3);
});
```

```php
// tests/Unit/Models/UserTest.php
use App\Models\User;

it('returns full name via accessor', function () {
    $user = User::factory()->create([
        'first_name' => 'Marie',
        'last_name' => 'Dupont',
    ]);
    expect($user->full_name)->toBe('Marie Dupont');
});

it('uses soft deletes', function () {
    $user = User::factory()->create();
    $user->delete();
    $this->assertSoftDeleted($user);
    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});
```

## Data Models

### New Factory: InstagramAccessTokenFactory

```php
<?php

namespace Database\Factories;

use App\Models\InstagramAccessToken;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstagramAccessTokenFactory extends Factory
{
    protected $model = InstagramAccessToken::class;

    public function definition(): array
    {
        return [
            'access_token' => encrypt('fake-access-token-' . $this->faker->uuid()),
            'expires_at' => now()->addMonths(3),
        ];
    }
}
```

### New Factory: InstagramMediaFactory

```php
<?php

namespace Database\Factories;

use App\Models\InstagramMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstagramMediaFactory extends Factory
{
    protected $model = InstagramMedia::class;

    public function definition(): array
    {
        return [
            'media_id' => $this->faker->unique()->uuid(),
            'caption' => $this->faker->sentence(),
            'media_type' => $this->faker->randomElement(['IMAGE', 'CAROUSEL_ALBUM']),
            'media_url' => $this->faker->url(),
            'permalink' => $this->faker->url(),
            'timestamp' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
```

### New Factory: ProductOptionFactory

```php
<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductOptionFactory extends Factory
{
    protected $model = ProductOption::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(rand(1, 3), true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'has_price' => true,
            'product_id' => Product::factory(),
            'is_active' => true,
        ];
    }
}
```

No existing models or migrations need changes.

## Error Handling

### Controller Bug Fix Required

**DestroyProductController** — remove `dd($medias)` on line 12. Without this fix, the destroy endpoint returns a dump instead of deleting. The test will catch this immediately.

### Test Assertions for Error Responses

All validation tests assert:
- HTTP 422 (`assertUnprocessable()`)
- `assertJsonValidationErrors([...])` for specific fields

Contact form error tests assert:
- HTTP 422 for validation/spam
- HTTP 500 for mail sending failure
- Specific message strings in JSON response

## Testing Strategy

### Execution Order

1. Fix `DestroyProductController` bug (remove `dd()`)
2. Create 3 new factories (InstagramAccessToken, InstagramMedia, ProductOption)
3. Update `tests/Pest.php` (enable RefreshDatabase, remove placeholder code)
4. Delete old test files (`BasicTest.php`, `mail/MailTest.php`)
5. Rename existing test folders (`Categories/` → `Category/`, `Users/` → `User/`)
6. Write and rewrite Feature tests (CRUD controllers, contact forms, Instagram, commands)
7. Write Unit tests (models, services)
8. Run full suite: `php artisan test`

### Test Count Estimate

| Area | Files | Tests |
|------|-------|-------|
| User CRUD | 5 | ~15 |
| Page CRUD | 5 | ~14 |
| Category CRUD | 5 | ~14 |
| Product CRUD | 5 | ~16 |
| Gallery CRUD | 5 | ~14 |
| Instagram Media | 1 | ~3 |
| Contact Forms | 2 | ~12 |
| Console Commands | 2 | ~2 |
| Models | 8 | ~20 |
| Services | 6 | ~10 |
| **Total** | **44** | **~120** |

### Test Running Commands

```bash
# Full suite
php artisan test

# Single file
php artisan test tests/Feature/Http/Controllers/Api/Product/StoreProductControllerTest.php

# Filter by name
php artisan test --filter="creates a product"

# Parallel execution
php artisan test --parallel
```

## Performance Considerations

- `RefreshDatabase` uses transactions (not `migrate:fresh`) — fast teardown
- Factories use `HasFactory` trait — already implemented on all models
- `Http::fake()` prevents real HTTP calls in service tests
- `Mail::fake()` prevents real mail in contact form tests
- No file I/O in tests (Spatie media tests skipped for collections that require real files)
- `BCRYPT_ROUNDS=4` in phpunit.xml — fast password hashing

## Security Considerations

- No real API tokens or secrets in test code — use `encrypt('fake-token')`
- Config values overridden via `config([...])` in `beforeEach`
- No real email addresses (use `@example.com` per RFC 2606)
- `phpunit.xml` already sets `MAIL_MAILER=array` preventing accidental real mail

## Monitoring and Observability

- `php artisan test --coverage` for code coverage report (requires Xdebug or PCOV)
- CI integration: run `php artisan test` in pipeline
- Test names are descriptive and follow the pattern: `it('verb + what is tested', ...)`
- Failed tests output both expected and actual values via Pest's expectation API
