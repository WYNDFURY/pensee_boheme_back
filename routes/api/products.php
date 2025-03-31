<?php

use App\Http\Controllers\Product\DestroyProductController;
use App\Http\Controllers\Product\IndexProductController;
use App\Http\Controllers\Product\ShowProductController;
use App\Http\Controllers\Product\StoreProductController;
use App\Http\Controllers\Product\UpdateProductController;
use Illuminate\Support\Facades\Route;

// Products Routes
Route::prefix('products')->name('products.')->group(function () {
    Route::post('/', StoreProductController::class)->name('store');
    Route::patch('/{product}', UpdateProductController::class)->name('update');
    Route::get('/{product}', ShowProductController::class)->name('show');
    Route::delete('/{product}', DestroyProductController::class)->name('destroy');
    Route::get('/', IndexProductController::class)->name('index');
});
