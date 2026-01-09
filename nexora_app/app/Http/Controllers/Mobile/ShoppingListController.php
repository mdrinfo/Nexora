<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\ShoppingListItem;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ShoppingListController extends Controller
{
    public function index()
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->first();
        
        // 1. Sync low stock items (Auto-add)
        $lowStockItems = InventoryItem::whereColumn('quantity', '<=', 'min_threshold')->get();

        foreach ($lowStockItems as $item) {
            // Target stock is 3x the threshold (safe buffer)
            $targetStock = $item->min_threshold > 0 ? $item->min_threshold * 3 : 10;
            $needed = max(0, $targetStock - $item->quantity);

            ShoppingListItem::firstOrCreate(
                [
                    'inventory_item_id' => $item->id,
                    'tenant_id' => $tenant->id
                ],
                [
                    'quantity_needed' => $needed,
                    'is_manual' => false
                ]
            );
        }

        $list = ShoppingListItem::with('inventoryItem.supplier')
            ->orderBy('is_checked') // Unchecked first
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mobile.shopping_list.index', compact('list'));
    }

    public function toggle(ShoppingListItem $item)
    {
        $item->update(['is_checked' => !$item->is_checked]);
        return response()->json(['success' => true, 'is_checked' => $item->is_checked]);
    }

    public function updateQuantity(Request $request, ShoppingListItem $item)
    {
        $data = $request->validate(['quantity' => 'required|numeric|min:0']);
        $item->update(['quantity_needed' => $data['quantity']]);
        return response()->json(['success' => true]);
    }

    public function confirm()
    {
        $checkedItems = ShoppingListItem::where('is_checked', true)->with('inventoryItem')->get();
        $count = $checkedItems->count();

        foreach ($checkedItems as $listItem) {
            if ($listItem->inventoryItem) {
                $listItem->inventoryItem->increment('quantity', $listItem->quantity_needed);
                // Log audit/history here if needed
            }
            $listItem->delete();
        }

        return redirect()->back()->with('success', "$count articles ajoutés au stock.");
    }

    public function store(Request $request)
    {
        // Manually add item
        $tenant = Tenant::query()->where('slug', 'nexora')->first();
        
        $data = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
        ]);

        ShoppingListItem::updateOrCreate(
            [
                'inventory_item_id' => $data['inventory_item_id'],
                'tenant_id' => $tenant->id
            ],
            [
                'quantity_needed' => $data['quantity'],
                'is_manual' => true,
                'is_checked' => false
            ]
        );

        return redirect()->back()->with('success', 'Article ajouté à la liste.');
    }
}
