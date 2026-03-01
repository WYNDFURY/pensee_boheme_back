<?php

namespace App\Http\Controllers\User;

use App\Http\Resources\UserResource;
use App\Models\User;

class ShowUserController
{
  public function __invoke(User $user)
  {
    return new UserResource($user);
  }
}
