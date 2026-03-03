<?php

use App\Http\Controllers\User\DestroyUserController;
use App\Http\Controllers\User\IndexUserController;
use App\Http\Controllers\User\ShowUserController;
use App\Http\Controllers\User\StoreUserController;
use App\Http\Controllers\User\UpdateUserController;
use Illuminate\Support\Facades\Route;

// Users Routes
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', IndexUserController::class)->name('index');
    Route::get('/{user}', ShowUserController::class)->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', StoreUserController::class)->name('store');
        Route::patch('/{user}', UpdateUserController::class)->name('update');
        Route::delete('/{user}', DestroyUserController::class)->name('destroy');
    });
});
