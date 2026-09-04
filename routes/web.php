<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/consumables')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');
});

require __DIR__.'/consumables.php';
require __DIR__.'/settings.php';
