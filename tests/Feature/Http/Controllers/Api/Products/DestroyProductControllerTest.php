<?php

use function Pest\Laravel\delete;
use App\Models\Product;


it('deletes a product', function () {
    $product = Product::factory()->create();
    $response = delete("/api/products/{$product->id}");
    $response->assertOk();

    $this->assertSoftDeleted($product);
});
