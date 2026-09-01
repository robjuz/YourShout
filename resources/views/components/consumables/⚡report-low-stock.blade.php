<?php

use App\Models\Consumable;
use App\Notifications\NextToBringNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

new class extends \Livewire\Component
{
    public Consumable $consumable;

    public function report()
    {
        $this->consumable->markAsLowStock();

        // Notify the next person to bring
        $nextUser = $this->consumable->nextToBring();
        if ($nextUser) {
            Notification::route('mail', $nextUser->email)
                ->notify(new NextToBringNotification($this->consumable, $nextUser));
        }

        session()->flash('success', __('consumables.low_stock_reported'));
    }
};
?>

<flux:button variant="warning" wire:click="report" icon="exclamation-triangle">
    {{ __('consumables.report_low_stock') }}
</flux:button>
