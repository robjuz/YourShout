<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', 'consumables')->name('dashboard');
});

require __DIR__.'/consumables.php';
require __DIR__.'/settings.php';
