<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Floor;
use App\Models\DiningTable;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FloorUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_floor_with_empty_tables()
    {
        // Arrange: Create Tenant, User (Admin), Floor, and some Tables
        $tenant = Tenant::create(['name' => 'Test Tenant', 'slug' => 'test-tenant', 'default_currency' => 'EUR']);
        // Ensure ID is 1 because controller hardcodes it
        if ($tenant->id !== 1) {
            $tenant->id = 1;
            $tenant->save();
        }

        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@nexora.local',
            'password' => bcrypt('password'),
        ]);

        $floor = Floor::create([
            'tenant_id' => 1,
            'name' => 'Test Floor',
            'level' => 1
        ]);

        DiningTable::create([
            'tenant_id' => 1,
            'floor_id' => $floor->id,
            'label' => 'T1',
            'x_position' => 10,
            'y_position' => 10,
            'width' => 50,
            'height' => 50,
            'shape' => 'square',
            'capacity' => 4
        ]);

        $this->assertCount(1, $floor->tables);

        // Act: Send POST request with empty tables
        $response = $this->actingAs($user)
                         ->postJson(route('admin.floors.update_tables', $floor), [
                             'tables' => []
                         ]);

        // Assert: Check response and DB
        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertCount(0, $floor->fresh()->tables);
    }

    public function test_can_update_floor_with_new_tables()
    {
        // Arrange
        $tenant = Tenant::create(['name' => 'Test Tenant', 'slug' => 'test-tenant', 'default_currency' => 'EUR']);
        if ($tenant->id !== 1) {
            $tenant->id = 1;
            $tenant->save();
        }

        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@nexora.local',
            'password' => bcrypt('password'),
        ]);

        $floor = Floor::create([
            'tenant_id' => 1,
            'name' => 'Test Floor',
            'level' => 1
        ]);

        $tablesData = [
            [
                'id' => null, // New table
                'label' => 'New T1',
                'x' => 100,
                'y' => 100,
                'width' => 60,
                'height' => 60,
                'shape' => 'round',
                'capacity' => 2,
                'rotation' => 0
            ]
        ];

        // Act
        $response = $this->actingAs($user)
                         ->postJson(route('admin.floors.update_tables', $floor), [
                             'tables' => $tablesData
                         ]);

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertCount(1, $floor->fresh()->tables);
        $this->assertEquals('New T1', $floor->fresh()->tables->first()->label);
    }
}
