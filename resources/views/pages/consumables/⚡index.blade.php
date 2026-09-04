<?php

use App\Models\Consumable;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends \Livewire\Component {

    #[Computed]
    public function consumables(): Collection
    {
        return Consumable::with(['users' => function ($query) {
            $query->withPivot('total_quantity')
                ->orderByPivot('total_quantity', 'asc');
        }])
            ->orderByDesc('updated_at')
            ->get();
    }
};
?>

<div class="space-y-6">
    @if ($this->consumables->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500">{{ __('consumables.no_consumables') }}</p>
            <p class="text-sm text-gray-400 mt-1 mb-4">{{ __('consumables.create_first') }}</p>
            <a href="{{ route('consumables.create') }}" wire:navigate>
                <flux:button variant="outline" icon="plus">{{ __('consumables.new_consumable') }}</flux:button>
            </a>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 justify-center">
            @foreach ($this->consumables as $consumable)
                <livewire:consumables.consumable :$consumable wire:key="{{ $consumable->id }}"/>
            @endforeach

            <div class="flex justify-center items-center">
                <flux:button href="{{ route('consumables.create') }}" icon="plus" square wire:navigate
                             title="{{ __('consumables.new_consumable') }}"/>
            </div>

        </div>
    @endif
</div>
