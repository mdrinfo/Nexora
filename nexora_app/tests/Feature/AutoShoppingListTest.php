<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\InventoryItem;
use App\Models\ShoppingListItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AutoShoppingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_automatically_adds_to_shopping_list()
    {
        // 1. Setup Tenant
        $tenant = Tenant::create([
            'name' => 'Nexora Test', 
            'slug' => 'nexora',
            'default_currency' => 'EUR',
            'default_language' => 'fr'
        ]);

        // 2. Create Item with initial high stock
        $item = InventoryItem::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Item',
            'category' => 'food',
            'unit' => 'kg',
            'quantity' => 100,
            'min_threshold' => 10,
            'cost_price' => 10.00,
        ]);

        // Assert not in shopping list
        $this->assertDatabaseMissing('shopping_list_items', [
            'inventory_item_id' => $item->id,
        ]);

        // 3. Update stock to be low (below threshold)
        $item->update(['quantity' => 5]);

        // 4. Assert added to shopping list
        // Target = 10 * 3 = 30. Needed = 30 - 5 = 25.
        $this->assertDatabaseHas('shopping_list_items', [
            'inventory_item_id' => $item->id,
            'quantity_needed' => 25,
            'is_manual' => false,
        ]);
    }

    public function test_creating_low_stock_item_adds_to_shopping_list()
    {
        $tenant = Tenant::create([
            'name' => 'Nexora Test', 
            'slug' => 'nexora',
            'default_currency' => 'EUR',
            'default_language' => 'fr'
        ]);

        // Create item directly with low stock
        $item = InventoryItem::create([
            'tenant_id' => $tenant->id,
            'name' => 'Low Stock Item',
            'category' => 'food',
            'unit' => 'kg',
            'quantity' => 5,
            'min_threshold' => 10,
            'cost_price' => 10.00,
        ]);

        $this->assertDatabaseHas('shopping_list_items', [
            'inventory_item_id' => $item->id,
            'quantity_needed' => 25, // (10*3) - 5
        ]);
    }
}
