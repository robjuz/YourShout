<?php

use App\Models\Consumable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends \Livewire\Component {

    #[Computed]
    public function joinedConsumables()
    {
        return auth()->user()
            ->consumables()
            ->with(['users' => function (BelongsToMany $query) {
                $query->withPivot('total_quantity')
                    ->orderByPivot('total_quantity', 'asc');
            }])
            ->orderByDesc('updated_at')
            ->get();
    }
};
?>

<div class="space-y-6">
    <div>
        <flux:heading size="xl" level="1">{{ __('consumables.my_consumables') }}</flux:heading>
        <flux:text class="text-gray-500">{{ __('consumables.joined_summary') }}</flux:text>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($this->joinedConsumables as $consumable)
            <livewire:consumables.consumable :$consumable @leave="$refresh"/>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500">{{ __('consumables.no_joined') }}</p>
                <p class="text-sm text-gray-400 mt-1">
                    <a href="{{ route('consumables.index') }}" class="text-indigo-600 hover:underline" wire:navigate>
                        {{ __('consumables.browse_all') }}
                    </a>
                </p>
            </div>
        @endforelse
    </div>
</div>
