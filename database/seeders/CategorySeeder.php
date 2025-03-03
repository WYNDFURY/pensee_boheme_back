<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Get the created pages
    $pages = Page::all();

    // Create 4 categories, distributed between the available pages
    foreach ($pages as $page) {
      $categoriesToCreate = $page->id == 1 ? 2 : 2; // 2 categories per page
      Category::factory($categoriesToCreate)->create([
        'page_id' => $page->id,
      ]);
    }
  }
}
