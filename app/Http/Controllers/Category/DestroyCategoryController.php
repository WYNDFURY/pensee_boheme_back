<?php

namespace App\Http\Controllers\Category;

use App\Models\Category;

class DestroyCategoryController
{
  public function __invoke(Category $category)
  {
    $category->delete();

    return response()->json(['message' => 'Category deleted'], 200);
  }
}
