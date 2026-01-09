<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Session;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function place(Session $session, ?User $user, array $items): Order
    {
        return DB::transaction(function () use ($session, $user, $items) {
            $order = Order::create([
                'tenant_id' => $session->tenant_id,
                'session_id' => $session->id,
                'user_id' => $user?->id,
                'status' => 'pending',
                'subtotal' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
            ]);

            $subtotal = 0;

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $unitPrice = (float) $product->price;
                $lineTotal = $qty * $unitPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $lineTotal;

                foreach ($product->ingredients as $ingredient) {
                    $deductQty = ($ingredient->pivot->quantity ?? 0) * $qty;
                    InventoryItem::where('id', $ingredient->id)
                        ->decrement('quantity', $deductQty);
                }
            }

            $taxTotal = 0;
            $grandTotal = $subtotal + $taxTotal;

            $order->update([
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
            ]);

            return $order->fresh('items');
        });
    }
}

