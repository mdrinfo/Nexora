<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\InventoryItem;
use App\Models\Tenant;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::query()->with('supplier');
        
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $items = $query->orderBy('name')->get();
        
        // Calculate low stock items for alert
        $lowStockCount = InventoryItem::query()
            ->whereColumn('quantity', '<=', 'min_threshold')
            ->count();

        return view('admin.inventory.index', [
            'items' => $items,
            'lowStockCount' => $lowStockCount
        ]);
    }

    public function create()
    {
        $suppliers = Supplier::query()->orderBy('name')->get();
        return view('admin.inventory.create', ['suppliers' => $suppliers]);
    }

    public function store(Request $request)
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:food,drink,cleaning,other'],
            'unit' => ['required', 'string', 'max:10'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'min_threshold' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('inventory', 'public');
        }

        InventoryItem::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'category' => $data['category'],
            'unit' => $data['unit'],
            'quantity' => $data['quantity'],
            'min_threshold' => $data['min_threshold'],
            'cost_price' => $data['cost_price'],
            'supplier_id' => $data['supplier_id'],
            'image_path' => $imagePath,
            'last_audited_at' => now(),
        ]);

        return redirect()->route('admin.inventory.index')->with('success', 'Article ajouté au stock.');
    }

    public function edit(InventoryItem $inventory)
    {
        $suppliers = Supplier::query()->orderBy('name')->get();
        return view('admin.inventory.edit', ['item' => $inventory, 'suppliers' => $suppliers]);
    }

    public function update(Request $request, InventoryItem $inventory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:food,drink,cleaning,other'],
            'unit' => ['required', 'string', 'max:10'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'min_threshold' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($inventory->image_path) {
                Storage::disk('public')->delete($inventory->image_path);
            }
            $inventory->image_path = $request->file('image')->store('inventory', 'public');
        }

        $inventory->update([
            'name' => $data['name'],
            'category' => $data['category'],
            'unit' => $data['unit'],
            'quantity' => $data['quantity'],
            'min_threshold' => $data['min_threshold'],
            'cost_price' => $data['cost_price'],
            'supplier_id' => $data['supplier_id'],
        ]);

        return redirect()->route('admin.inventory.index')->with('success', 'Article mis à jour.');
    }

    public function destroy(InventoryItem $inventory)
    {
        if ($inventory->image_path) {
            Storage::disk('public')->delete($inventory->image_path);
        }
        $inventory->delete();
        return redirect()->route('admin.inventory.index')->with('success', 'Article supprimé.');
    }

    public function audit()
    {
        $threshold = now()->subDays(2);
        
        $items = InventoryItem::query()
            ->where(function($q) use ($threshold) {
                $q->whereNull('last_audited_at')
                  ->orWhere('last_audited_at', '<', $threshold);
            })
            ->orderBy('category')
            ->get();
            
        return view('admin.inventory.audit', ['items' => $items]);
    }
    
    public function updateAudit(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:inventory_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ]);
        
        foreach ($data['items'] as $itemData) {
            InventoryItem::where('id', $itemData['id'])->update([
                'quantity' => $itemData['quantity'],
                'last_audited_at' => now(),
            ]);
        }
        
        return redirect()->route('admin.inventory.index')->with('success', 'Inventaire vérifié et mis à jour.');
    }
}
