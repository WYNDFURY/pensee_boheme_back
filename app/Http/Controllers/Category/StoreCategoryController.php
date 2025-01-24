<?php

namespace App\Http\Controllers\Category;

use App\Models\Category;
use Illuminate\Http\Request;

class StoreCategoryController
{
  public function __invoke(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
    ]);

    $category = Category::create($validated);

    return response()->json($category, 201);
  }
}
