<?php

use App\Models\Consumable;
use App\Models\PurchaseRecord;
use Livewire\Component;

new class extends \Livewire\Component
{
    public Consumable $consumable;
    public int $quantity = 1;

    protected $rules = [
        'quantity' => ['required', 'integer', 'min:1'],
    ];

    public function record()
    {
        $this->validate();

        PurchaseRecord::create([
            'consumable_id' => $this->consumable->id,
            'user_id' => auth()->id(),
            'quantity' => $this->quantity,
            'reported_at' => now(),
        ]);

        // Update pivot total_quantity
        $currentTotal = auth()->user()
            ->consumables()
            ->where('consumables.id', $this->consumable->id)
            ->first()
            ->pivot->total_quantity;

        auth()->user()->consumables()->updateExistingPivot(
            $this->consumable->id,
            ['total_quantity' => $currentTotal + $this->quantity]
        );

        // Resolve low stock
        $this->consumable->resolveLowStock();

        session()->flash('success', __('consumables.purchase_recorded'));

        $this->quantity = 1;
    }
};
?>

<form wire:submit="record" class="flex items-end gap-2">
    <flux:input
        wire:model="quantity"
        type="number"
        min="1"
        label="{{ __('consumables.quantity') }}"
        class="w-20"
    />
    <flux:button type="submit" variant="primary" icon="shopping-cart">{{ __('consumables.record_purchase') }}</flux:button>
</form>
