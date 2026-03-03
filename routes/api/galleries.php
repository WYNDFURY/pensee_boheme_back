<?php

use App\Http\Controllers\Gallery\DestroyGalleryController;
use App\Http\Controllers\Gallery\IndexGalleryController;
use App\Http\Controllers\Gallery\ShowGalleryController;
use App\Http\Controllers\Gallery\StoreGalleryController;
use App\Http\Controllers\Gallery\UpdateGalleryController;
use Illuminate\Support\Facades\Route;

// Galleries Routes
Route::prefix('galleries')->name('galleries.')->group(function () {
    Route::get('/', IndexGalleryController::class)->name('index');
    Route::get('/{slug}', ShowGalleryController::class)->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', StoreGalleryController::class)->name('store');
        Route::patch('/{gallery:slug}', UpdateGalleryController::class)->name('update');
        Route::delete('/{gallery:slug}', DestroyGalleryController::class)->name('destroy');
    });
});
