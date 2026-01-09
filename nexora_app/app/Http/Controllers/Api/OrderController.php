<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Session;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\InventoryItem;
use App\Models\User;
use App\Events\OrderReady;
use App\Events\OrderClaimed;

class OrderController extends Controller
{
    public function addItem(Request $request, Session $session)
    {
        $data = $request->validate([
            'order_id' => ['nullable', 'integer'],
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'waiter_id' => ['required', 'integer'],
            'special_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $waiter = User::query()->find($data['waiter_id']);
        if (!$waiter) {
            return response()->json(['error' => 'invalid_waiter'], 422);
        }
        $product = Product::query()->find($data['product_id']);
        if (!$product) {
            return response()->json(['error' => 'invalid_product'], 422);
        }

        $order = null;
        if (!empty($data['order_id'])) {
            $order = Order::query()->where('session_id', $session->id)->find($data['order_id']);
            if (!$order) {
                return response()->json(['error' => 'order_not_found'], 404);
            }
        } else {
            $order = Order::query()->create([
                'tenant_id' => $session->tenant_id,
                'session_id' => $session->id,
                'user_id' => $waiter->id,
                'status' => 'pending',
                'subtotal' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
            ]);
        }

        $created = DB::transaction(function () use ($order, $product, $data, $session) {
            $unit = $product->price;
            $qty = $data['quantity'];
            $line = $unit * $qty;

            $item = OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_price' => $unit,
                'line_total' => $line,
                'waiter_id' => $data['waiter_id'],
                'special_notes' => $data['special_notes'] ?? null,
                'status' => 'pending',
            ]);

            $order->subtotal += $line;
            $order->grand_total = $order->subtotal + $order->tax_total;
            $order->save();

            $session->total_amount = DB::table('orders')->where('session_id', $session->id)->sum('grand_total');
            $session->save();

            // Stock Deduction
            foreach ($product->ingredients as $ingredient) {
                $deductQty = ($ingredient->pivot->quantity ?? 0) * $qty;
                if ($deductQty > 0) {
                    InventoryItem::where('id', $ingredient->id)->decrement('quantity', $deductQty);
                }
            }

            return $item;
        });

        return response()->json([
            'order_id' => $order->id,
            'item_id' => $created->id,
            'status' => 'pending',
        ], 201);
    }

    public function markReady(Order $order)
    {
        $order->status = 'ready';
        $order->save();
        event(new OrderReady($order));
        return response()->json(['ok' => true, 'order_id' => $order->id, 'status' => 'ready']);
    }

    public function claim(Request $request, Order $order)
    {
        $data = $request->validate([
            'waiter_id' => ['required', 'integer'],
        ]);
        $order->claimed_by_user_id = $data['waiter_id'];
        $order->claimed_at = Carbon::now();
        $order->save();
        event(new OrderClaimed($order));
        return response()->json(['ok' => true, 'order_id' => $order->id, 'claimed_by' => $order->claimed_by_user_id]);
    }

    public function served(Order $order)
    {
        $order->status = 'served';
        $order->save();
        return response()->json(['ok' => true, 'order_id' => $order->id, 'status' => 'served']);
    }
}
