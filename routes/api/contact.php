<?php

use App\Http\Controllers\CreationContactFormController;
use App\Http\Controllers\EventContactFormController;
use Illuminate\Support\Facades\Route;

// Users Routes
Route::prefix('contact')->name('contact.')->group(function () {
    Route::post('/creation', [CreationContactFormController::class, 'creation']);
    Route::post('/event', [EventContactFormController::class, 'creation']);
    // Route::get('/preview-mail/event', function () {
    //     $sampleData = [
    //         'firstName' => 'Jean',
    //         'lastName' => 'Dupont',
    //         'email' => 'jean.dupont@example.com',
    //         'phone' => '01 23 45 67 89',
    //         'eventDate' => '2025-08-15',
    //         'eventLocation' => 'Château de Versailles',
    //         'themeColors' => 'Rose et blanc',
    //         'message' => 'Bonjour, je souhaiterais organiser un mariage avec une décoration florale élégante...Bonjour, je souhaiterais organiser un mariage avec une décoration florale élégante...Bonjour, je souhaiterais organiser un mariage avec une décoration florale élégante...Bonjour, je souhaiterais organiser un mariage avec une décoration florale élégante...Bonjour, je souhaiterais organiser un mariage avec une décoration florale élégante...',
    //     ];

    //     return new \App\Mail\EventContactFormMail($sampleData);
    // });
});
