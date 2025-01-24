<?php

namespace App\Http\Controllers\Image;

use App\Models\Image;

class DestroyImageController
{
  public function __invoke(Image $image)
  {
    $image->delete();

    return response()->json(['message' => 'Image deleted'], 200);
  }
}
