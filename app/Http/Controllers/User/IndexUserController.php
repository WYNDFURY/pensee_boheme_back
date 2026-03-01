<?php

namespace App\Http\Controllers\User;

use App\Http\Resources\UserResource;
use App\Models\User;

class IndexUserController
{
  public function __invoke()
  {
    return UserResource::collection(User::all());
  }
}
