<?php

namespace App\Http\Controllers\Image;

use App\Models\Image;

class ShowImageController
{
  public function __invoke(Image $image)
  {

    $image->load(['category', 'product']);

    return response()->json($image);
  }
}
