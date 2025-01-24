<?php

namespace App\Http\Controllers\Category;

use App\Models\Category;

class ShowCategoryController
{
  public function __invoke(Category $category)
  {
    return response()->json($category);
  }
}
