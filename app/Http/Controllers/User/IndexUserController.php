<?php

namespace App\Http\Controllers\User;

use App\Models\User;

class IndexUserController
{
  public function __invoke()
  {
    $users = User::all();

    return response()->json($users);
  }
}
