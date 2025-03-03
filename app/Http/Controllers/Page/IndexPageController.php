<?php

namespace App\Http\Controllers\Page;

use App\Models\Page;

class IndexPageController
{
    public function __invoke()
    {
        $pages = Page::all();

        return response()->json($pages);
    }
}
