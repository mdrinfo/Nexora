<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Session;
use App\Models\DiningTable;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\QrKey;

class StockDeductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ordering_product_deducts_stock()
    {
        // 1. Setup Tenant
        $tenant = Tenant::create([
            'name' => 'Nexora Test', 
            'slug' => 'nexora',
            'default_currency' => 'EUR',
            'default_language' => 'fr'
        ]);

        // 2. Setup Inventory Items (Ingredients)
        $coffeeBean = InventoryItem::create([
            'tenant_id' => $tenant->id,
            'name' => 'Coffee Beans',
            'category' => 'food',
            'unit' => 'g',
            'quantity' => 1000.00, // Initial Stock: 1000g
            'min_threshold' => 100,
            'cost_price' => 0.02,
        ]);

        $milk = InventoryItem::create([
            'tenant_id' => $tenant->id,
            'name' => 'Milk',
            'category' => 'drink',
            'unit' => 'ml',
            'quantity' => 1000.00, // Initial Stock: 1000ml
            'min_threshold' => 200,
            'cost_price' => 0.001,
        ]);

        // 3. Setup Product (Cappuccino)
        $category = Category::create(['tenant_id' => $tenant->id, 'name' => 'Hot Drinks', 'is_drink' => true]);
        $product = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'name' => 'Cappuccino',
            'price' => 3.50,
            'is_active' => true,
        ]);

        // 4. Link Ingredients (Recipe)
        // Cappuccino uses 18g coffee and 150ml milk
        $product->ingredients()->attach([
            $coffeeBean->id => ['quantity' => 18, 'unit' => 'g'],
            $milk->id => ['quantity' => 150, 'unit' => 'ml'],
        ]);

        // 5. Setup User (Waiter) and Session
        $waiter = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'John Waiter',
            'email' => 'waiter@test.com',
            'password' => bcrypt('password'),
        ]);
        
        $table = DiningTable::create([
            'tenant_id' => $tenant->id, 
            'label' => 'Table 1',
            'capacity' => 4
        ]);
        
        $qrKey = QrKey::create([
            'tenant_id' => $tenant->id,
            'dining_table_id' => $table->id,
            'token' => 'test-token',
            'status' => 'active'
        ]);

        $session = Session::create([
            'tenant_id' => $tenant->id,
            'dining_table_id' => $table->id,
            'qr_key_id' => $qrKey->id,
            'status' => 'open',
            'currency' => 'EUR',
            'opened_at' => now(),
        ]);

        // 6. Perform Action: Place Order via API
        // Order 2 Cappuccinos
        $response = $this->postJson("/api/orders/{$session->id}/add-item", [
            'product_id' => $product->id,
            'quantity' => 2,
            'waiter_id' => $waiter->id,
        ]);

        $response->assertStatus(201);

        // 7. Verify Stock Deduction
        // Coffee: 1000 - (18 * 2) = 1000 - 36 = 964
        // Milk: 1000 - (150 * 2) = 1000 - 300 = 700

        $this->assertDatabaseHas('inventory_items', [
            'id' => $coffeeBean->id,
            'quantity' => 964.00,
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $milk->id,
            'quantity' => 700.00,
        ]);
    }
}
