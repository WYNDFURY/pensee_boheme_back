<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

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
        $category->load('page');

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category),
        ]);
    }
}
