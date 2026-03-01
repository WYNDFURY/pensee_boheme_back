<?php

use function Pest\Laravel\get;
use App\Models\Page;
use App\Models\Category;
use App\Models\Product;

it('returns a page by slug', function () {
    $page = Page::factory()->create();

    get("/api/pages/{$page->slug}")
        ->assertOk()
        ->assertJsonPath('slug', $page->slug);
});

it('returns page with nested categories and active products', function () {
    $page = Page::factory()->create();
    $category = Category::factory()->create(['page_id' => $page->id, 'order' => 1]);
    Product::factory()->create(['category_id' => $category->id, 'is_active' => true, 'name' => 'Active Product']);
    Product::factory()->create(['category_id' => $category->id, 'is_active' => false, 'name' => 'Inactive Product']);

    $response = get("/api/pages/{$page->slug}");

    $response->assertOk()
        ->assertJsonCount(1, 'categories')
        ->assertJsonCount(1, 'categories.0.products');
});

it('returns 404 for nonexistent slug', function () {
    get('/api/pages/nonexistent-slug')->assertNotFound();
});
