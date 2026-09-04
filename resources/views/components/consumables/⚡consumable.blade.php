<?php

use App\Models\Consumable;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Consumable $consumable;

    #[Computed]
    public function hasJoined()
    {
        return auth()->user()->consumables()->where('consumables.id', $this->consumable->id)->exists();
    }

    public function join()
    {
        $user = auth()->user();

        if ($user->consumables()->where('consumables.id', $this->consumable->id)->doesntExist()) {
            $user->consumables()->attach($this->consumable, ['total_quantity' => 0]);
        }

        session()->flash('success', __('consumables.joined_successfully'));

//        return redirect()->route('consumables.show', $consumable);
    }

    public function leave()
    {
        auth()->user()->consumables()->detach($this->consumable);

        $this->dispatch('leave');

//        session()->flash('success', __('consumables.left_successfully'));

//        return redirect()->route('dashboard');
    }
};
?>

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

    <div class="flex gap-2 pt-2 mt-auto">
        @if ($this->hasJoined)
            <a href="{{ route('consumables.show', $consumable) }}" class="flex-1">
                <flux:button variant="primary" class="w-full">{{ __('consumables.participation') }}</flux:button>
            </a>

            <flux:button wire:click="leave" variant="outline">{{ __('consumables.leave') }}</flux:button>
        @else
            <flux:button wire:click="join" variant="primary" class="w-full">{{ __('consumables.join') }}</flux:button>
        @endif
    </div>
</flux:card>
