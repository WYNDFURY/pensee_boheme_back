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

use App\Http\Controllers\Product\DestroyProductController;
use App\Http\Controllers\Product\IndexProductController;
use App\Http\Controllers\Product\ShowProductController;
use App\Http\Controllers\Product\StoreProductController;
use App\Http\Controllers\Product\UpdateProductController;
use App\Http\Controllers\Category\DestroyCategoryController;
use App\Http\Controllers\Category\IndexCategoryController;
use App\Http\Controllers\Category\ShowCategoryController;
use App\Http\Controllers\Category\StoreCategoryController;
use App\Http\Controllers\Category\UpdateCategoryController;
use App\Http\Controllers\Image\DestroyImageController;
use App\Http\Controllers\Image\IndexImageController;
use App\Http\Controllers\Image\ShowImageController;
use App\Http\Controllers\Image\StoreImageController;
use App\Http\Controllers\Image\UpdateImageController;
use App\Http\Controllers\User\DestroyUserController;
use App\Http\Controllers\User\IndexUserController;
use App\Http\Controllers\User\ShowUserController;
use App\Http\Controllers\User\StoreUserController;
use App\Http\Controllers\User\UpdateUserController;


Route::name('api.')->middleware(['throttle:60,1'])->group(function () {

    /// Products Routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::post('/', StoreProductController::class)->name('store');
        Route::patch('/{product}', UpdateProductController::class)->name('update');
        Route::get('/{product}', ShowProductController::class)->name('show');
        Route::delete('/{product}', DestroyProductController::class)->name('destroy');
        Route::get('/', IndexProductController::class)->name('index');
    });

    // Categories Routes
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::post('/', StoreCategoryController::class)->name('store');
        Route::patch('/{category}', UpdateCategoryController::class)->name('update');
        Route::get('/{category}', ShowCategoryController::class)->name('show');
        Route::delete('/{category}', DestroyCategoryController::class)->name('destroy');
        Route::get('/', IndexCategoryController::class)->name('index');
    });

    // Images Routes
    Route::prefix('images')->name('images.')->group(function () {
        Route::post('/', StoreImageController::class)->name('store');
        Route::patch('/{image}', UpdateImageController::class)->name('update');
        Route::get('/{image}', ShowImageController::class)->name('show');
        Route::delete('/{image}', DestroyImageController::class)->name('destroy');
        Route::get('/', IndexImageController::class)->name('index');
    });

    // Users Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::post('/', StoreUserController::class)->name('store');
        Route::patch('/{user}', UpdateUserController::class)->name('update');
        Route::get('/{user}', ShowUserController::class)->name('show');
        Route::delete('/{user}', DestroyUserController::class)->name('destroy');
        Route::get('/', IndexUserController::class)->name('index');
    });
});
