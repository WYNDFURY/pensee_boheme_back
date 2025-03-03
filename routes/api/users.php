<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DestroyUserController;
use App\Http\Controllers\User\IndexUserController;
use App\Http\Controllers\User\ShowUserController;
use App\Http\Controllers\User\StoreUserController;
use App\Http\Controllers\User\UpdateUserController;

// Users Routes
Route::prefix('users')->name('users.')->group(function () {
  Route::post('/', StoreUserController::class)->name('store');
  Route::patch('/{user}', UpdateUserController::class)->name('update');
  Route::get('/{user}', ShowUserController::class)->name('show');
  Route::delete('/{user}', DestroyUserController::class)->name('destroy');
  Route::get('/', IndexUserController::class)->name('index');
});
