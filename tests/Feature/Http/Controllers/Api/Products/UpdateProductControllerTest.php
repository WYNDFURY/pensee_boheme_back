<?php

use function Pest\Laravel\patch;
use App\Models\Product;

it('updates an existing product', function () {
    $product = Product::factory()->create();

    $updatedData = [
        'name' => 'Peigne Fleur Deluxemama',
        'description' => 'Description de mama',
        'price' => 23,
        'category_id' => $product->category_id, // Ensure this category exists in your test database
    ];

    $response = patch("/api/products/{$product->id}", $updatedData);

    $response->assertOk()
        ->assertJsonPath('name', 'Peigne Fleur Deluxemama')
        ->assertJsonPath('description', 'Description de mama')
        ->assertJsonPath('price', 23)
        ->assertJsonPath('category_id', $product->category_id);

    // Ensure the product is updated in the database
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Peigne Fleur Deluxemama',
        'description' => 'Description de mama',
        'price' => 23,
        'category_id' => $product->category_id,
    ]);
});
