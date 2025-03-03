<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StoreCategoryController extends Controller
{
  public function __invoke(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'order' => 'nullable|integer',
      'page_id' => 'required|integer|exists:pages,id',
    ]);

    $category = Category::create($validated);

    return response()->json([
      'message' => 'Category created successfully',
      'category' => $category,
    ], 201);
  }
}
