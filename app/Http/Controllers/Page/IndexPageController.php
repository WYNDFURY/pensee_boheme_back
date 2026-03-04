<?php

namespace App\Http\Controllers\Page;

use App\Http\Resources\PageResource;
use App\Models\Page;

class IndexPageController
{
    public function __invoke()
    {
        return PageResource::collection(Page::all());
    }
}
