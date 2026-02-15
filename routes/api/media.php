<?php

use App\Http\Controllers\Media\DestroyMediaController;
use Illuminate\Support\Facades\Route;

// Media Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/media/{media}', DestroyMediaController::class)->name('media.destroy');
});
