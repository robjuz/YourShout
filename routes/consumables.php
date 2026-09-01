<?php

use App\Models\Consumable;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('consumables')->name('consumables.')->group(function () {
        Route::livewire('/', 'consumables/index')->name('index');
        Route::livewire('/create', 'consumables/create')->name('create');
        Route::livewire('/{consumable}', 'consumables/show')->name('show');
        Route::post('/{consumable}/join', function (Consumable $consumable) {
            auth()->user()->consumables()->attach($consumable, ['total_quantity' => 0]);

            session()->flash('success', __('consumables.joined_successfully'));

            return redirect()->route('consumables.show', $consumable);
        })->name('join');
        Route::delete('/{consumable}', function (Consumable $consumable) {
            auth()->user()->consumables()->detach($consumable);

            session()->flash('success', __('consumables.left_successfully'));

            return redirect()->route('dashboard');
        })->name('leave');
    });
});
