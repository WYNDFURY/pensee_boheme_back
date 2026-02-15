<?php

use App\Models\ProductOption;
use App\Models\Product;

it('belongs to a product', function () {
    $option = ProductOption::factory()->create();
    expect($option->product)->toBeInstanceOf(Product::class);
});

it('uses soft deletes', function () {
    $option = ProductOption::factory()->create();
    $option->delete();
    $this->assertSoftDeleted('product_options', ['id' => $option->id]);
});
