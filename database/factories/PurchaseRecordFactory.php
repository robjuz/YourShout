<?php

namespace Database\Factories;

use App\Models\Consumable;
use App\Models\PurchaseRecord;
use App\Models\User;
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
            'consumable_id' => Consumable::factory(),
            'user_id' => User::factory(),
            'quantity' => $this->faker->numberBetween(1, 6),
        ];
    }
}
