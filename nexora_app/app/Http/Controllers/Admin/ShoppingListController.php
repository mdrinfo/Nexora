<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\InventoryItem;
use App\Models\ShoppingListItem;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ShoppingListController extends Controller
{
    public function index()
    {
        // 1. Auto-add low stock items if not already in list
        $this->refreshAutoItems();

        $items = ShoppingListItem::query()
            ->with(['inventoryItem.supplier'])
            ->orderBy('is_checked') // Unchecked first
            ->orderByDesc('is_manual') // Manual items first (usually urgent)
            ->get();

        return view('admin.shopping_list.index', ['items' => $items]);
    }

    public function store(Request $request)
    {
        // Add manual item to list
        $data = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity_needed' => ['required', 'numeric', 'min:0.1'],
        ]);
        
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();

        ShoppingListItem::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'inventory_item_id' => $data['inventory_item_id']
            ],
            [
                'quantity_needed' => $data['quantity_needed'],
                'is_manual' => true,
                'is_checked' => false
            ]
        );

        return redirect()->back()->with('success', 'Article ajouté à la liste.');
    }

    public function toggleCheck(ShoppingListItem $item)
    {
        $item->update(['is_checked' => !$item->is_checked]);
        return response()->json(['success' => true, 'is_checked' => $item->is_checked]);
    }

    public function syncStock()
    {
        // "Confirm & Update Stock"
        DB::transaction(function () {
            $checkedItems = ShoppingListItem::query()->where('is_checked', true)->get();

            foreach ($checkedItems as $listItem) {
                $inventoryItem = $listItem->inventoryItem;
                if ($inventoryItem) {
                    $inventoryItem->increment('quantity', $listItem->quantity_needed);
                }
                $listItem->delete();
            }
        });

        return redirect()->route('admin.shopping_list')->with('success', 'Stock mis à jour avec les articles achetés !');
    }
    
    public function clear()
    {
         ShoppingListItem::query()->truncate();
         return redirect()->back()->with('success', 'Liste vidée.');
    }

    private function refreshAutoItems()
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->first();
        if (!$tenant) return;

        $lowStockItems = InventoryItem::query()
            ->whereColumn('quantity', '<=', 'min_threshold')
            ->get();

        foreach ($lowStockItems as $item) {
            // Check if already in list
            $exists = ShoppingListItem::query()
                ->where('inventory_item_id', $item->id)
                ->exists();

            if (!$exists) {
                // Suggest quantity to reach 2x min_threshold or a default amount
                // For simplicity, let's ask for (Threshold * 2) - Current
                $needed = ($item->min_threshold * 2) - $item->quantity;
                if ($needed <= 0) $needed = $item->min_threshold; // Fallback

                ShoppingListItem::create([
                    'tenant_id' => $tenant->id,
                    'inventory_item_id' => $item->id,
                    'quantity_needed' => $needed,
                    'is_manual' => false,
                    'is_checked' => false,
                ]);
            }
        }
    }
}
