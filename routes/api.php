<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::name('api.')->middleware(['throttle:60,1'])->group(function () {
    // Include model-specific route files
    require __DIR__.'/api/products.php';
    require __DIR__.'/api/categories.php';
    require __DIR__.'/api/users.php';
    require __DIR__.'/api/pages.php';
    require __DIR__.'/api/galleries.php';
    require __DIR__.'/api/instagram.php';
    require __DIR__.'/api/contact.php';
});
