<?php

namespace App\Http\Controllers\User;

use App\Models\User;

class ShowUserController
{
  public function __invoke(User $user)
  {
    return response()->json($user);
  }
}
