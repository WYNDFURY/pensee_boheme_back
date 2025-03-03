<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Category\DestroyCategoryController;
use App\Http\Controllers\Category\IndexCategoryController;
use App\Http\Controllers\Category\ShowCategoryController;
use App\Http\Controllers\Category\StoreCategoryController;
use App\Http\Controllers\Category\UpdateCategoryController;

// Categories Routes
Route::prefix('categories')->name('categories.')->group(function () {
  Route::post('/', StoreCategoryController::class)->name('store');
  Route::patch('/{category}', UpdateCategoryController::class)->name('update');
  Route::get('/{category}', ShowCategoryController::class)->name('show');
  Route::delete('/{category}', DestroyCategoryController::class)->name('destroy');
  Route::get('/', IndexCategoryController::class)->name('index');
});
