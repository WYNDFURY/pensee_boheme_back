<?php

namespace App\Http\Controllers\Page;

use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class StorePageController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:pages',
        ]);

        $page = Page::create($validated);

        return response()->json([
            'message' => 'Page created successfully',
            'data' => new PageResource($page),
        ], 201);
    }
}
