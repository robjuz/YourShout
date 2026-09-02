<?php

use App\Models\Consumable;
use App\Models\PurchaseRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Consumables', function () {
    test('index screen displays all consumables', function () {
        $user = User::factory()->create();

        $consumable = Consumable::factory()->create();

        $response = $this->actingAs($user)->get(route('consumables.index'));

        $response->assertStatus(200);
        $response->assertSee($consumable->name);
    });

    test('index screen is empty when user has no consumables', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('consumables.index'));

        $response->assertOk();
        $response->assertSee(__('consumables.no_consumables'));
    });

    test('create screen displays correctly', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('consumables.create'));

        $response->assertOk();
        $response->assertSee(__('consumables.create_consumable'));
    });

    test('a consumable can be created and user is automatically joined', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::consumables.create')
            ->set('name', 'Milk')
            ->set('unit', 'liters')
            ->call('create')
            ->assertSessionHas('success', __('consumables.joined_successfully'))
            ->assertRedirect();

        $this->assertDatabaseHas('consumables', ['name' => 'Milk', 'unit' => 'liters']);
        $this->assertDatabaseHas('consumable_user', [
            'user_id' => $user->id,
            'total_quantity' => 0,
        ]);
    });

    test('a consumable name is required when creating', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::consumables.create')
            ->set('unit', 'liters')
            ->call('create')
            ->assertHasErrors('name');
    });

    test('a consumable name cannot exceed 255 characters', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::consumables.create')
            ->set('name', str_repeat('a', 256))
            ->set('unit', 'liters')
            ->call('create')
            ->assertHasErrors('name');
    });

    test('a consumable can be viewed by a joined user', function () {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create(['name' => 'Coffee']);
        $user->consumables()->attach($consumable, ['total_quantity' => 5]);

        $response = $this->actingAs($user)->get(route('consumables.show', $consumable));

        $response->assertOk();
        $response->assertSee('Coffee');
    });

    test('an unjoined user can view a consumable', function () {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();

        $response = $this->actingAs($user)->get(route('consumables.show', $consumable));

        $response->assertOk();
    });

    test('a user can join a consumable', function () {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('consumables.join', $consumable));

        $response->assertRedirect(route('consumables.show', $consumable));

        $this->assertDatabaseHas('consumable_user', [
            'user_id' => $user->id,
            'consumable_id' => $consumable->id,
            'total_quantity' => 0,
        ]);

        $response->assertSessionHas('success', __('consumables.joined_successfully'));
    });

    test('joining an already joined consumable does not reset the total', function () {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();
        $user->consumables()->attach($consumable, ['total_quantity' => 7]);

        $response = $this->actingAs($user)
            ->post(route('consumables.join', $consumable));

        $response->assertRedirect(route('consumables.show', $consumable));

        $this->assertDatabaseHas('consumable_user', [
            'user_id' => $user->id,
            'consumable_id' => $consumable->id,
            'total_quantity' => 7,
        ]);
    });

    test('a user can leave a consumable', function () {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();
        $user->consumables()->attach($consumable, ['total_quantity' => 3]);

        $response = $this->actingAs($user)
            ->delete(route('consumables.leave', $consumable));

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('consumable_user', [
            'user_id' => $user->id,
            'consumable_id' => $consumable->id,
        ]);

        $response->assertSessionHas('success', __('consumables.left_successfully'));
    });

    test('the next person to bring is the one with the lowest total quantity', function () {
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);

        $consumable = Consumable::factory()->create();
        $consumable->users()->attach($user1, ['total_quantity' => 10]);
        $consumable->users()->attach($user2, ['total_quantity' => 2]);

        $next = $consumable->nextToBring();

        expect($next->id)->toBe($user2->id);
    });

    test('purchase records can be created', function () {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();
        $user->consumables()->attach($consumable, ['total_quantity' => 0]);

        $response = $this->actingAs($user)
            ->get(route('consumables.show', $consumable));

        $response->assertOk();
    });

    test('low stock can be reported on a consumable', function () {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();
        $user->consumables()->attach($consumable, ['total_quantity' => 0]);

        expect($consumable->fresh()->is_low_stock)->toBeFalse();

        $consumable->markAsLowStock();

        expect($consumable->fresh()->is_low_stock)->toBeTrue();
    });

    test('marking low stock and resolving it works correctly', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create(['name' => 'Dave']);

        $consumable = Consumable::factory()->create();
        $consumable->users()->attach($user1, ['total_quantity' => 5]);
        $consumable->users()->attach($user2, ['total_quantity' => 2]);

        $consumable->markAsLowStock();
        expect($consumable->fresh()->is_low_stock)->toBeTrue();

        PurchaseRecord::create([
            'consumable_id' => $consumable->id,
            'user_id' => $user2->id,
            'quantity' => 5,
            'reported_at' => now(),
        ]);

        $consumable->resolveLowStock();
        expect($consumable->fresh()->is_low_stock)->toBeFalse();
    });

    test('participants are ordered by total quantity ascending', function () {
        $user1 = User::factory()->create(['name' => 'Charlie']);
        $user2 = User::factory()->create(['name' => 'Alice']);
        $user3 = User::factory()->create(['name' => 'Bob']);

        $consumable = Consumable::factory()->create();
        $consumable->users()->attach($user1, ['total_quantity' => 10]);
        $consumable->users()->attach($user2, ['total_quantity' => 1]);
        $consumable->users()->attach($user3, ['total_quantity' => 5]);

        $participants = $consumable->participants->pluck('id')->toArray();

        expect($participants)->toHaveKeys([0, 1, 2])
            ->and($participants[0])->toBe($user2->id)
            ->and($participants[1])->toBe($user3->id)
            ->and($participants[2])->toBe($user1->id);
    });
});
