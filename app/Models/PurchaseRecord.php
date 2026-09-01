<?php

namespace App\Models;

use Database\Factories\PurchaseRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $consumable_id
 * @property int $user_id
 * @property int $quantity
 * @property string|null $reported_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['consumable_id', 'user_id', 'quantity', 'reported_at'])]
#[Hidden([])]
class PurchaseRecord extends Model
{
    /** @use HasFactory<PurchaseRecordFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
        ];
    }

    /**
     * Get the consumable this purchase record belongs to.
     */
    public function consumable(): BelongsTo
    {
        return $this->belongsTo(Consumable::class);
    }

    /**
     * Get the user who made this purchase.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
