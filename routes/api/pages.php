<?php

use App\Http\Controllers\Page\DestroyPageController;
use App\Http\Controllers\Page\IndexPageController;
use App\Http\Controllers\Page\ShowPageController;
use App\Http\Controllers\Page\StorePageController;
use App\Http\Controllers\Page\UpdatePageController;
use Illuminate\Support\Facades\Route;

// Pages Routes
Route::prefix('pages')->name('pages.')->group(function () {
    Route::post('/', StorePageController::class)->name('store');
    Route::patch('/{page:slug}', UpdatePageController::class)->name('update');
    Route::get('/{page:slug}', ShowPageController::class)->name('show');
    Route::delete('/{page:slug}', DestroyPageController::class)->name('destroy');
    Route::get('/', IndexPageController::class)->name('index');
});
