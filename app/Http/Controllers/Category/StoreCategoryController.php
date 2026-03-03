<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class StoreCategoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
            'page_id' => 'required|integer|exists:pages,id',
        ]);

        $category = Category::create($validated);
        $category->load('page');

        return response()->json([
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
        ], 201);
    }
}
