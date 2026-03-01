<?php

namespace App\Http\Controllers\Category;

use App\Http\Resources\CategoryResource;
use App\Models\Category;

class ShowCategoryController
{
  public function __invoke(Category $category)
  {
    $category->load('page');

    return new CategoryResource($category);
  }
}
