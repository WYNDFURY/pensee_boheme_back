<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Gallery\DestroyGalleryController;
use App\Http\Controllers\Gallery\IndexGalleryController;
use App\Http\Controllers\Gallery\ShowGalleryController;
use App\Http\Controllers\Gallery\StoreGalleryController;
use App\Http\Controllers\Gallery\UpdateGalleryController;

// Galleries Routes
Route::prefix('galleries')->name('galleries.')->group(function () {
  Route::post('/', StoreGalleryController::class)->name('store');
  Route::patch('/{gallery}', UpdateGalleryController::class)->name('update');
  Route::get('/{gallery}', ShowGalleryController::class)->name('show');
  Route::delete('/{gallery}', DestroyGalleryController::class)->name('destroy');
  Route::get('/', IndexGalleryController::class)->name('index');
});
