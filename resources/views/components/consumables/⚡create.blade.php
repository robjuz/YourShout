<?php

use App\Models\Consumable;
use Livewire\Component;

new class extends \Livewire\Component
{
    public string $name = '';
    public string $unit = 'pieces';

    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
        'unit' => ['required', 'string', 'max:50'],
    ];

    public function create()
    {
        $this->validate();

        $consumable = Consumable::create([
            'name' => $this->name,
            'unit' => $this->unit,
            'is_low_stock' => false,
        ]);

        auth()->user()->consumables()->attach($consumable, ['total_quantity' => 0]);

        session()->flash('success', __('consumables.joined_successfully'));

        return redirect()->route('consumables.show', $consumable);
    }
};
?>

<div class="max-w-2xl mx-auto">
    <flux:heading size="xl" level="1">{{ __('consumables.create_consumable') }}</flux:heading>
    <flux:separator variant="subtle" class="mb-6" />

    <form wire:submit="create" class="space-y-6">
        <flux:card>
            <div class="space-y-4">
                <flux:input
                    wire:model="name"
                    label="{{ __('consumables.consumable_name') }}"
                    placeholder="e.g., Milk, Coffee, Toilet Paper"
                    required
                />

                <flux:input
                    wire:model="unit"
                    label="{{ __('consumables.consumable_unit') }}"
                    placeholder="e.g., pieces, liters, kg"
                    required
                />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex justify-end gap-3">
                <a href="{{ route('dashboard') }}">
                    <flux:button variant="outline">{{ __('actions.cancel') }}</flux:button>
                </a>
                <flux:button type="submit" variant="primary">{{ __('consumables.create_consumable') }}</flux:button>
            </div>
        </flux:card>
    </form>
</div>
