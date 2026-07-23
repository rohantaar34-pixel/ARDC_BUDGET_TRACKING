<?php

use App\Models\InventoryItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('inventory deletion uses the application confirmation modal', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    InventoryItem::create([
        'name' => 'PIKO',
        'unit' => 'pcs',
        'unit_cost' => 100,
        'quantity' => 2,
    ]);

    $this->actingAs($admin)
        ->get(route('inventory.index'))
        ->assertOk()
        ->assertSee('id="appConfirmModal"', false)
        ->assertSee('data-confirm-title="Delete inventory item?"', false)
        ->assertDontSee('return confirm(', false);
});

test('ledger transaction deletion uses the application confirmation modal', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $project = Project::create([
        'name' => 'Modal Test Project',
        'budget' => 1000,
        'status' => 'not_started',
    ]);

    $this->actingAs($admin)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('id="appConfirmModal"', false)
        ->assertDontSee('return confirm(', false);
});
