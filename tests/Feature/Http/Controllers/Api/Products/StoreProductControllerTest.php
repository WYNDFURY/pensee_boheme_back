<?php

use function Pest\Laravel\post;

use App\Models\Category;

it('creates a new product with valid data', function () {
    // Create a category to associate with the product
    $category = Category::factory()->create();

    $data = [
        'name' => 'Peigne Fleur Exemple',
        'description' => 'Sample description',
        'price' => 20,
        'category_id' => $category->id,
    ];

    $response = post('/api/products', $data);

    $response->assertCreated()
        ->assertJsonPath('name', 'Peigne Fleur Exemple')
        ->assertJsonPath('description', 'Sample description')
        ->assertJsonPath('price', 20)
        ->assertJsonPath('category_id', $category->id);

    // Ensure the product is created in the database
    $this->assertDatabaseHas('products', [
        'name' => 'Peigne Fleur Exemple',
        'description' => 'Sample description',
        'price' => 20,
        'category_id' => $category->id,
    ]);
});
