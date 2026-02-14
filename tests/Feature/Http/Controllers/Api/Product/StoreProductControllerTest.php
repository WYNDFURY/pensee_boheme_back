<?php

use function Pest\Laravel\postJson;
use App\Models\Category;
use App\Models\Product;

it('creates a product with valid data', function () {
    $category = Category::factory()->create();

    $response = postJson('/api/products', [
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
    postJson('/api/products', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'slug', 'category_id']);
});

it('rejects duplicate slug', function () {
    Product::factory()->create(['slug' => 'existing-slug']);
    $category = Category::factory()->create();

    postJson('/api/products', [
        'name' => 'Another',
        'slug' => 'existing-slug',
        'category_id' => $category->id,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

it('rejects nonexistent category_id', function () {
    postJson('/api/products', [
        'name' => 'Test',
        'slug' => 'test',
        'category_id' => 9999,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

it('rejects negative price', function () {
    $category = Category::factory()->create();

    postJson('/api/products', [
        'name' => 'Test',
        'slug' => 'test',
        'category_id' => $category->id,
        'price' => -5,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['price']);
});
