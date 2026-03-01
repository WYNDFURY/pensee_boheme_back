<?php

namespace App\Http\Controllers\Page;

use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class UpdatePageController
{
    public function __invoke(Request $request, Page $page)
    {
        $validated = $request->validate([
            'slug' => 'sometimes|string|max:255',
        ]);

        $page->update($validated);

        return response()->json([
            'message' => 'Page updated successfully',
            'data' => new PageResource($page),
        ]);
    }
}
