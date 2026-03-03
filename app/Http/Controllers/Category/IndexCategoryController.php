<?php

namespace App\Http\Controllers\Category;

use App\Http\Resources\CategoryResource;
use App\Models\Category;

class IndexCategoryController
{
    public function __invoke()
    {
        return CategoryResource::collection(Category::all());
    }
}
