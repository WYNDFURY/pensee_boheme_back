<?php

namespace App\Http\Controllers\Gallery;

use App\Models\Gallery;

class ShowGalleryController
{
  public function __invoke(Gallery $gallery)
  {
    return response()->json($gallery);
  }
}
