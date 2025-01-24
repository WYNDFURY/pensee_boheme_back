<?php

namespace App\Http\Controllers\Image;

use App\Models\Image;

class IndexImageController
{
  public function __invoke()
  {
    $images = Image::all();

    return response()->json($images);
  }
}
