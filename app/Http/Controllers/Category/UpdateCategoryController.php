<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UpdateCategoryController extends Controller
{
  public function __invoke(Request $request, Category $category)
  {

    $validated = $request->validate([
      'name' => 'sometimes|string|max:255',
      'description' => 'sometimes|nullable|string',
      'order' => 'sometimes|nullable|integer',
      'page_id' => 'sometimes|integer|exists:pages,id',
    ]);

    $category->update($validated);

    return response()->json([
      'message' => 'Category updated successfully',
      'category' => $category,
    ]);
  }
}
