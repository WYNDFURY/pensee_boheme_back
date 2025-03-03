<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Get the created categories
    $categories = Category::all();

    // Create 20 products distributed across all categories
    foreach ($categories as $category) {
      $productsPerCategory = 5; // 5 products per category (5 * 4 = 20)
      Product::factory($productsPerCategory)->create([
        'category_id' => $category->id,
      ]);
    }
  }
}
