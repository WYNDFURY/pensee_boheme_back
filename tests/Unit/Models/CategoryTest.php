<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;

it('belongs to a page', function () {
    $category = Category::factory()->create();
    expect($category->page)->toBeInstanceOf(Page::class);
});

it('has many products', function () {
    $category = Category::factory()->create();
    Product::factory()->count(2)->create(['category_id' => $category->id]);
    expect($category->products)->toHaveCount(2);
});

it('uses soft deletes', function () {
    $category = Category::factory()->create();
    $category->delete();
    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});
