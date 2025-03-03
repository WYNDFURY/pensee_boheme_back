<?php

namespace App\Http\Controllers\Page;

use App\Models\Page;

class ShowPageController
{
    public function __invoke(Page $page)
    {
        return response()->json($page);
    }
}
