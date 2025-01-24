<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;

class UpdateUserController
{
  public function __invoke(Request $request, User $user)
  {
    $validated = $request->validate([
      'first_name' => 'sometimes|string|max:255',
      'last_name' => 'sometimes|string|max:255',
      'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
      'password' => 'sometimes|string|min:8',
    ]);

    if (isset($validated['password'])) {
      $validated['password'] = bcrypt($validated['password']);
    }

    $user->update($validated);

    return response()->json($user);
  }
}
