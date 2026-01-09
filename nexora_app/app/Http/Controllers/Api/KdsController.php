<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use App\Models\OrderItem;
use App\Events\OrderItemReady;

class KdsController extends Controller
{
    public function kitchenQueue()
    {
        $items = OrderItem::query()
            ->with(['order.session.diningTable', 'product.category'])
            ->whereIn('status', ['pending', 'preparing', 'ready'])
            ->whereHas('product.category', function ($q) {
                $q->where('destination', 'kitchen');
            })
            ->get();

        return response()->json($items->map(function($item) {
             return [
                 'id' => $item->id,
                 'quantity' => $item->quantity,
                 'product' => $item->product,
                 'table' => $item->order->session->diningTable->label ?? '?',
                 'status' => $item->status,
                 'elapsed_min' => $item->created_at->diffInMinutes(now()),
                 'special_notes' => null // Add if needed
             ];
        }));
    }

    public function barQueue()
    {
        $items = OrderItem::query()
            ->with(['order.session.diningTable', 'product.category'])
            ->whereIn('status', ['pending', 'preparing', 'ready'])
            ->whereHas('product.category', function ($q) {
                $q->where('destination', 'bar');
            })
            ->get();

        return response()->json($items->map(function($item) {
             return [
                 'id' => $item->id,
                 'quantity' => $item->quantity,
                 'product' => $item->product,
                 'table' => $item->order->session->diningTable->label ?? '?',
                 'status' => $item->status,
                 'elapsed_min' => $item->created_at->diffInMinutes(now()),
                 'special_notes' => null
             ];
        }));
    }

    public function updateStatus(Request $request, OrderItem $item)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:pending,preparing,ready,served'],
        ]);
        $item->status = $data['status'];
        if ($data['status'] === 'ready') {
            $item->waiter_notified_at = Carbon::now();
            event(new OrderItemReady($item));
        }
        $item->save();
        return response()->json(['ok' => true, 'status' => $item->status]);
    }
}
