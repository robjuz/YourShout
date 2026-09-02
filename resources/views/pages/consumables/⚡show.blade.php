<?php

use App\Models\Consumable;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends \Livewire\Component
{
    public Consumable $consumable;

    public function mount(Consumable $consumable)
    {
        $this->consumable = $consumable;
    }

    public function isJoined(): bool
    {
        return auth()->user()->consumables()->where('consumables.id', $this->consumable->id)->exists();
    }

    #[Computed]
    public function members()
    {
        return $this->consumable
            ->users()
            ->withPivot('total_quantity')
            ->orderByPivot('total_quantity', 'asc')
            ->get();
    }

    #[Computed]
    public function purchaseHistory()
    {
        return $this->consumable
            ->purchases()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }
};
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <flux:heading size="xl" level="1">{{ $consumable->name }}</flux:heading>
            <p class="text-gray-500 mt-1">{{ $consumable->unit }}</p>
        </div>
        @if ($consumable->is_low_stock)
            <flux:badge variant="warning">{{ __('consumables.low_stock') }}</flux:badge>
        @endif
    </div>

    <flux:separator variant="subtle" />

    @if (!$this->isJoined())
        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold">{{ __('consumables.join') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('consumables.participation') }}</p>
                </div>
                <form method="POST" action="{{ route('consumables.join', $consumable) }}">
                    @csrf
                    <flux:button type="submit" variant="primary">{{ __('consumables.join') }}</flux:button>
                </form>
            </div>
        </flux:card>
    @else
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Members Table -->
            <flux:card>
                <flux:heading size="lg">{{ __('consumables.members') }}</flux:heading>
                <flux:separator variant="subtle" class="mb-4" />

                @if ($this->members->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('consumables.no_consumables') }}</p>
                @else
                    <div class="space-y-2">
                        @foreach ($this->members as $user)
                            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="xs" name="{{ $user->name }}" />
                                    <div>
                                        <p class="font-medium text-sm">{{ $user->name }}</p>
                                        @if ($user->id === $consumable->nextToBring()?->id)
                                            <p class="text-xs text-indigo-600">{{ __('consumables.next_to_bring') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-sm font-medium">{{ $user->pivot->total_quantity }} {{ $consumable->unit }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <flux:separator variant="subtle" class="my-4" />

                <livewire:consumables.record-purchase :consumable="$consumable" />

                <flux:separator variant="subtle" class="my-4" />

                <livewire:consumables.report-low-stock :consumable="$consumable" />

                <flux:separator variant="subtle" class="my-4" />

{{--                <form method="POST" action="{{ route('consumables.leave', $consumable) }}">--}}
{{--                    @csrf--}}
{{--                    @method('DELETE')--}}
{{--                    <flux:button variant="outline" type="submit">{{ __('consumables.leave') }}</flux:button>--}}
{{--                </form>--}}
            </flux:card>

{{--            <!-- Purchase History -->--}}
{{--            <flux:card>--}}
{{--                <flux:heading size="lg">{{ __('consumables.purchase_history') }}</flux:heading>--}}
{{--                <flux:separator variant="subtle" class="mb-4" />--}}

{{--                @if ($this->purchaseHistory->isEmpty())--}}
{{--                    <p class="text-sm text-gray-500">{{ __('consumables.no_purchase_history') }}</p>--}}
{{--                @else--}}
{{--                    <div class="space-y-3">--}}
{{--                        @foreach ($this->purchaseHistory as $record)--}}
{{--                            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">--}}
{{--                                <div class="flex items-center gap-3">--}}
{{--                                    <flux:avatar size="xs" name="{{ $record->user->name }}" />--}}
{{--                                    <div>--}}
{{--                                        <p class="font-medium text-sm">{{ $record->user->name }}</p>--}}
{{--                                        <p class="text-xs text-gray-500">{{ $record->created_at->diffForHumans() }}</p>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <span class="text-sm font-medium">+{{ $record->quantity }} {{ $consumable->unit }}</span>--}}
{{--                            </div>--}}
{{--                        @endforeach--}}
{{--                    </div>--}}
{{--                @endif--}}
{{--            </flux:card>--}}
        </div>
    @endif
</div>
