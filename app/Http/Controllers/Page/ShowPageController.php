<?php

namespace App\Http\Controllers\Page;

use App\Http\Resources\PageResource;
use App\Models\Page;

class ShowPageController
{
    public function __invoke(Page $page)
    {
        $isAuthenticated = auth('sanctum')->check();

        $page->load([
            'categories' => fn ($query) => $query->orderBy('order', 'asc'),
            'categories.products' => function ($query) use ($isAuthenticated) {
                if (! $isAuthenticated) {
                    $query->where('is_active', true);
                }
                $query->orderBy('name', 'asc');
            },
            'categories.products.media' => fn ($query) => $query->where('collection_name', 'product_images'),
        ]);

        return new PageResource($page);
    }
}
