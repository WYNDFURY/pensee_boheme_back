<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;

it('belongs to a category', function () {
    $product = Product::factory()->create();
    expect($product->category)->toBeInstanceOf(Category::class);
});

it('has many options', function () {
    $product = Product::factory()->create();
    ProductOption::factory()->count(2)->create(['product_id' => $product->id]);
    expect($product->options)->toHaveCount(2);
});

it('uses soft deletes', function () {
    $product = Product::factory()->create();
    $product->delete();
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

it('casts is_active and has_price to boolean', function () {
    $product = Product::factory()->create(['is_active' => 1, 'has_price' => 0]);
    expect($product->is_active)->toBeTrue()
        ->and($product->has_price)->toBeFalse();
});

it('has active scope that filters by is_active', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    expect(Product::active()->count())->toBe(1);
});
