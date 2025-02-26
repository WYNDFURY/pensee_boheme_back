<?php

namespace App\Http\Controllers\Image;

use App\Models\Image;

class ShowImageController
{
  public function __invoke(Image $image)
  {

    $image->load('imageable');
    return response()->json($image);
  }
}
