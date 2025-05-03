<?php

use App\Http\Controllers\InstagramMedia\IndexInstagramMediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('instagram')->name('instagram.')->group(function () {
    Route::get('/', IndexInstagramMediaController::class)->name('index');
});
