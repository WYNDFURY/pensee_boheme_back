<?php

namespace App\Http\Controllers\User;

use App\Models\User;

class DestroyUserController
{
  public function __invoke(User $user)
  {
    $user->delete();

    return response()->json(['message' => 'User deleted'], 200);
  }
}
