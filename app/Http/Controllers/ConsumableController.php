<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use Illuminate\Http\RedirectResponse;

class ConsumableController extends Controller
{
    /**
     * Join the given consumable.
     */
    public function join(Consumable $consumable): RedirectResponse
    {
        $user = auth()->user();

        if ($user->consumables()->where('consumables.id', $consumable->id)->doesntExist()) {
            $user->consumables()->attach($consumable, ['total_quantity' => 0]);
        }

        session()->flash('success', __('consumables.joined_successfully'));

        return redirect()->route('consumables.show', $consumable);
    }

    /**
     * Leave the given consumable.
     */
    public function leave(Consumable $consumable): RedirectResponse
    {
        auth()->user()->consumables()->detach($consumable);

        session()->flash('success', __('consumables.left_successfully'));

        return redirect()->route('dashboard');
    }
}
