<?php

namespace App\Http\Controllers\Image;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class DestroyImageController
{
  public function __invoke(Image $image)
  {
    $image->delete();

    return response()->json(['message' => 'Image deleted'], 200);
  }
}
