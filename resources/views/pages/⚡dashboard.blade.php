<?php

use App\Models\Consumable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends \Livewire\Component
{
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

    public function totalPurchases()
    {
        return auth()->user()->totalPurchases();
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
            <flux:card class="flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-lg">{{ $consumable->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $consumable->unit }}</p>
                    </div>
                    @if ($consumable->is_low_stock)
                        <flux:badge variant="warning">{{ __('consumables.low_stock') }}</flux:badge>
                    @endif
                </div>

                <div class="text-sm text-gray-600">
                    {{ $consumable->users->count() }} {{ trans_choice('consumables.member', $consumable->users->count()) }}
                </div>

                @if ($consumable->nextToBring())
                    <div class="text-sm">
                        <span class="text-gray-500">{{ __('consumables.next_to_bring') }}:</span>
                        <span class="font-medium text-indigo-600">{{ $consumable->nextToBring()->name }}</span>
                    </div>
                @endif

                <div class="flex gap-2 pt-2">
                    <a href="{{ route('consumables.show', $consumable) }}" class="flex-1">
                        <flux:button variant="primary" class="w-full">{{ __('consumables.participation') }}</flux:button>
                    </a>
                </div>
            </flux:card>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500">{{ __('consumables.no_joined') }}</p>
                <p class="text-sm text-gray-400 mt-1"><a href="{{ route('consumables.index') }}" class="text-indigo-600 hover:underline">{{ __('consumables.browse_all') }}</a></p>
            </div>
        @endforelse
    </div>
</div>