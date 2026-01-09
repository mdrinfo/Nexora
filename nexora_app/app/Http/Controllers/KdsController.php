<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KdsController extends Controller
{
    public function index()
    {
        return view('kds.index', ['type' => 'kitchen']);
    }

    public function bar()
    {
        return view('kds.index', ['type' => 'bar']);
    }

    public function getItems(Request $request)
    {
        $type = $request->query('type', 'kitchen'); // kitchen or bar
        $tenantId = Auth::user()->tenant_id ?? 1;

        $items = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('sessions', 'orders.session_id', '=', 'sessions.id')
            ->leftJoin('dining_tables', 'sessions.dining_table_id', '=', 'dining_tables.id')
            ->where('orders.tenant_id', $tenantId)
            ->whereIn('order_items.status', ['pending', 'preparing', 'ready']) // Only active items
            ->where('categories.destination', $type)
            ->select(
                    'order_items.id',
                    'order_items.quantity',
                    'order_items.status',
                    'order_items.created_at',
                    'products.name as product_name',
                    'dining_tables.label as table_name',
                    'order_items.order_id'
                )
                ->with('options')
                ->orderBy('order_items.created_at', 'asc')
                ->get();

        // Group by Order
        $grouped = $items->groupBy('order_id')->map(function ($group) {
            $first = $group->first();
            
            // Determine Order Status based on items
            // If all ready -> Ready
            // If any preparing or ready -> Preparing
            // Else -> Pending
            $allReady = $group->every(function($i) { return $i->status === 'ready'; });
            $anyActive = $group->contains(function($i) { return in_array($i->status, ['preparing', 'ready']); });
            
            $status = 'pending';
            if ($allReady) {
                $status = 'ready';
            } elseif ($anyActive) {
                $status = 'preparing';
            }

            return [
                'order_id' => $first->order_id,
                'table_name' => $first->table_name,
                'session_type' => $first->session_type ?? 'table',
                'created_at' => $first->created_at,
                'status' => $status,
                'items' => $group->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'status' => $item->status,
                        'options' => $item->options
                    ];
                })->values()
            ];
        })->values();

        return response()->json($grouped);
    }

    public function updateStatus(Request $request, $id)
    {
        // $id can be an Item ID or an Order ID depending on action
        // For checklist toggles, it's Item ID.
        // For "Start Order", it might be Order ID? 
        // Let's stick to Item ID updates, but maybe allow batch updates.
        
        $itemId = $id;
        $item = OrderItem::findOrFail($itemId);
        $status = $request->input('status'); // 'preparing', 'ready'

        $item->update(['status' => $status]);

        // Check if all siblings in this order (for this destination) are ready
        // to notify waiter.
        if ($status === 'ready') {
            $order = $item->order;
            
            // Get destination of this item
            $destination = $item->product->category->destination ?? 'kitchen';

            $siblings = $order->items()
                ->whereHas('product.category', function($q) use ($destination) {
                    $q->where('destination', $destination);
                })
                ->get();

            $allReady = $siblings->every(function($i) { return $i->status === 'ready' || $i->status === 'served'; });

            if ($allReady) {
                // Mark all as notified? Or just log it.
                // The WaiterController::getReadyItems will pick this up if we group correctly.
                // We can timestamp the 'waiter_notified_at' on the items to avoid re-notifying if we want.
                foreach ($siblings as $sibling) {
                    $sibling->update(['waiter_notified_at' => now()]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
