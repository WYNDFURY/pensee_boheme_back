<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Page\DestroyPageController;
use App\Http\Controllers\Page\IndexPageController;
use App\Http\Controllers\Page\ShowPageController;
use App\Http\Controllers\Page\StorePageController;
use App\Http\Controllers\Page\UpdatePageController;

// Pages Routes
Route::prefix('pages')->name('pages.')->group(function () {
  Route::post('/', StorePageController::class)->name('store');
  Route::patch('/{page}', UpdatePageController::class)->name('update');
  Route::get('/{page}', ShowPageController::class)->name('show');
  Route::delete('/{page}', DestroyPageController::class)->name('destroy');
  Route::get('/', IndexPageController::class)->name('index');
});
