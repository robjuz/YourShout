<?php

namespace Database\Factories;

use App\Models\PurchaseRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseRecord>
 */
class PurchaseRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'consumable_id' => App\Models\Consumable::factory(),
            'user_id' => App\Models\User::factory(),
            'quantity' => $this->faker->numberBetween(1, 6),
        ];
    }
}
