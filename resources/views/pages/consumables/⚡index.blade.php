<?php

use App\Models\Consumable;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends \Livewire\Component
{
    #[Computed]
    public function consumables()
    {
        return Consumable::with(['users' => function ($query) {
            $query->withPivot('total_quantity')
                ->orderByPivot('total_quantity', 'asc');
        }])
            ->orderByDesc('updated_at')
            ->get();
    }

    #[Computed]
    public function joinedIds()
    {
        return auth()->user()->consumables()->pluck('consumables.id')->toArray();
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
                        @if (in_array($consumable->id, $this->joinedIds))
                            <a href="{{ route('consumables.show', $consumable) }}" class="flex-1">
                                <flux:button variant="primary" class="w-full">{{ __('consumables.participation') }}</flux:button>
                            </a>
                            <form method="POST" action="{{ route('consumables.leave', $consumable) }}">
                                @csrf
                                @method('DELETE')
                                <flux:button variant="outline">{{ __('consumables.leave') }}</flux:button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('consumables.join', $consumable) }}" class="w-full">
                                @csrf
                                <flux:button variant="primary" class="w-full">{{ __('consumables.join') }}</flux:button>
                            </form>
                        @endif
                    </div>
                </flux:card>
            @endforeach

            <div class="flex justify-center items-center">
                <flux:button href="{{ route('consumables.create') }}" icon="plus" square wire:navigate title="{{ __('consumables.new_consumable') }}"/>
            </div>

        </div>
    @endif
</div>
