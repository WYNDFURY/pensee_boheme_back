<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;

class StoreUserController
{
  public function __invoke(Request $request)
  {
    $validated = $request->validate([
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8',
    ]);

    $user = User::create([
      'first_name' => $validated['first_name'],
      'last_name' => $validated['last_name'],
      'email' => $validated['email'],
      'password' => bcrypt($validated['password']),
    ]);

    return response()->json($user, 201);
  }
}
