<?php

namespace App\Models;

use Database\Factories\ConsumableFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $unit
 * @property bool $is_low_stock
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'unit', 'is_low_stock'])]
#[Hidden([])]
class Consumable extends Model
{
    /** @use HasFactory<ConsumableFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_low_stock' => 'boolean',
        ];
    }

    /**
     * Get the users that have joined this consumable.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('total_quantity')
            ->using(ConsumableUser::class)
            ->orderBy('consumable_user.total_quantity', 'asc');
    }

    /**
     * Get the purchase records for this consumable.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(PurchaseRecord::class);
    }

    /**
     * Get the next user to bring this consumable (lowest total_quantity).
     */
    public function nextToBring(): ?User
    {
        return $this->users()->first();
    }

    /**
     * Check if there is a next person to bring this consumable.
     */
    public function hasNextToBring(): bool
    {
        return $this->users()->count() > 0;
    }

    /**
     * Scope a query to only include consumables that are low on stock.
     */
    public function scopeLowStock($query)
    {
        return $query->where('is_low_stock', true);
    }

    /**
     * Mark this consumable as low stock.
     */
    public function markAsLowStock(): void
    {
        $this->update(['is_low_stock' => true]);
    }

    /**
     * Resolve the low stock status after a purchase is recorded.
     */
    public function resolveLowStock(): void
    {
        if ($this->users()->count() > 0) {
            $this->update(['is_low_stock' => false]);
        }
    }

    /**
     * Get all users joined to this consumable with their totals, ordered by total_quantity ascending.
     */
    public function getParticipantsAttribute(): Collection
    {
        return $this->load('users')->users;
    }
}
