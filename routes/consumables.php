<?php

use App\Http\Controllers\ConsumableController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('consumables')->name('consumables.')->group(function () {
        Route::livewire('/', 'pages::consumables/index')->name('index');

        Route::livewire('/create', 'pages::consumables/create')->name('create');

        Route::livewire('/{consumable}', 'pages::consumables/show')->name('show');

        Route::post('/{consumable}/join', [ConsumableController::class, 'join'])->name('join');

        Route::delete('/{consumable}', [ConsumableController::class, 'leave'])->name('leave');
    });
});
