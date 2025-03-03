<?php

namespace App\Http\Controllers\Page;

use App\Models\Page;

class DestroyPageController
{
    public function __invoke(Page $page)
    {
        $page->delete();

        return response()->json(['message' => 'Page deleted'], 200);
    }
}
