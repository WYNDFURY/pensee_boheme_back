<?php

namespace App\Http\Controllers\Category;

use App\Models\Category;

class IndexCategoryController
{
  public function __invoke()
  {
    $categories = Category::all();

    return response()->json($categories);
  }
}
