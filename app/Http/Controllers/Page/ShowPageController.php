<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;

class ShowPageController extends Controller
{
    public function __invoke(Page $page)
    {
        // Eager load all relationships needed
        $page->load([
            'categories' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'categories.products' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('name', 'asc');
            },
            'categories.products.media' => function ($query) {
                $query->where('collection_name', 'product_images');
            },
            'categories.products.options',
        ]);

        // Return the resource
        return new PageResource($page);
    }
}
