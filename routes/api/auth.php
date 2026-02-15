<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class)->middleware('throttle:5,1')->name('login');
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum')->name('logout');
