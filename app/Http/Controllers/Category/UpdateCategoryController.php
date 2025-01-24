<?php

namespace App\Http\Controllers\Category;

use App\Models\Category;
use Illuminate\Http\Request;

class UpdateCategoryController
{
  public function __invoke(Request $request, Category $category)
  {
    $validated = $request->validate([
      'name' => 'sometimes|string|max:255',
    ]);

    $category->update($validated);

    return response()->json($category);
  }
}
