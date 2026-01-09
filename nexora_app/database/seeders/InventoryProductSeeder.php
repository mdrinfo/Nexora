<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Category;
use App\Models\Product;
use App\Models\InventoryItem;

class InventoryProductSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();

        $plats = Category::query()->firstOrCreate(['tenant_id' => $tenant->id, 'name' => 'Plats']);
        $boissons = Category::query()->firstOrCreate(['tenant_id' => $tenant->id, 'name' => 'Boissons']);
        $boissons->is_drink = true;
        $boissons->save();

        $tomate = InventoryItem::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Tomate'],
            ['unit' => 'g', 'quantity' => 10000, 'min_threshold' => 500]
        );
        $mozza = InventoryItem::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Mozzarella'],
            ['unit' => 'g', 'quantity' => 8000, 'min_threshold' => 500]
        );
        $pate = InventoryItem::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Pâte'],
            ['unit' => 'g', 'quantity' => 12000, 'min_threshold' => 500]
        );

        $pizza = Product::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Pizza Margherita'],
            ['category_id' => $plats->id, 'price' => 12.50, 'image_path' => 'storage/products/pizza_margherita.jpg', 'is_active' => true]
        );

        $pizza->ingredients()->syncWithoutDetaching([
            $tomate->id => ['quantity' => 150, 'unit' => 'g'],
            $mozza->id => ['quantity' => 120, 'unit' => 'g'],
            $pate->id => ['quantity' => 200, 'unit' => 'g'],
        ]);

        Product::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Cola'],
            ['category_id' => $boissons->id, 'price' => 3.00, 'image_path' => 'storage/products/cola.jpg', 'is_active' => true]
        );
    }
}
