<?php

use App\Http\Controllers\Page\DestroyPageController;
use App\Http\Controllers\Page\ShowPageController;
use App\Http\Controllers\Page\StorePageController;
use App\Http\Controllers\Page\UpdatePageController;
use Illuminate\Support\Facades\Route;

// Pages Routes
Route::prefix('pages')->name('pages.')->group(function () {
    Route::get('/{page:slug}', ShowPageController::class)->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', StorePageController::class)->name('store');
        Route::patch('/{page:slug}', UpdatePageController::class)->name('update');
        Route::delete('/{page:slug}', DestroyPageController::class)->name('destroy');
    });
});
