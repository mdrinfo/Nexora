<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function tableIndex($tableId)
    {
        $table = DiningTable::findOrFail($tableId);
        $products = $this->getProducts();

        return view('customer.menu', [
            'products' => $products,
            'table' => $table,
            'mode' => 'table',
            'sessionType' => 'table'
        ]);
    }

    public function onlineIndex()
    {
        $type = request('type', 'online'); 
        if (!in_array($type, ['online', 'takeaway'])) {
            $type = 'online';
        }

        $products = $this->getProducts();
        
        return view('customer.menu', [
            'products' => $products,
            'table' => null,
            'mode' => $type,
            'sessionType' => $type
        ]);
    }

    private function getProducts()
    {
        return Product::query()
            ->where('is_active', true)
            ->with(['category', 'optionGroups.options'])
            ->orderBy('name')
            ->get()
            ->groupBy('category.name');
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.options' => 'nullable|array',
            'items.*.options.*' => 'exists:product_options,id',
            'session_type' => 'required|in:table,online,takeaway',
            'table_id' => 'nullable|exists:dining_tables,id',
        ]);

        $sessionType = $request->session_type;
        $tableId = $request->table_id;
        $tenantId = 1; 

        if ($sessionType !== 'table') {
            $tableName = $sessionType === 'online' ? 'Online Order' : 'Takeaway Order';
            $table = DiningTable::firstOrCreate(
                ['label' => $tableName, 'tenant_id' => $tenantId],
                ['capacity' => 1]
            );
            $tableId = $table->id;
        }

        DB::beginTransaction();
        try {
            $session = null;
            
            if ($sessionType === 'table') {
                $session = Session::where('dining_table_id', $tableId)
                    ->where('status', 'open')
                    ->first();
            }

            if (!$session) {
                $qrKey = \App\Models\QrKey::create([
                    'tenant_id' => $tenantId,
                    'dining_table_id' => $tableId,
                    'token' => Str::random(32),
                    'status' => 'active'
                ]);

                $session = Session::create([
                    'tenant_id' => $tenantId,
                    'dining_table_id' => $tableId,
                    'qr_key_id' => $qrKey->id,
                    'status' => 'open',
                    'type' => $sessionType,
                    'currency' => 'EUR',
                    'opened_at' => now(),
                    'total_amount' => 0
                ]);
            }

            $order = Order::create([
                'tenant_id' => $tenantId,
                'session_id' => $session->id,
                'user_id' => null,
                'status' => 'pending',
                'subtotal' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
            ]);

            $orderTotal = 0;

            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['id']);
                
                $optionsTotal = 0;
                $validOptions = [];

                if (isset($itemData['options']) && is_array($itemData['options'])) {
                    foreach ($itemData['options'] as $optData) {
                        $optId = is_array($optData) ? ($optData['id'] ?? null) : $optData;
                        if ($optId) {
                            $option = \App\Models\ProductOption::with('group')->find($optId);
                            if ($option) {
                                $optionsTotal += $option->price_adjustment;
                                $validOptions[] = $option;
                            }
                        }
                    }
                }

                $unitPrice = $product->price + $optionsTotal;
                $lineTotal = $unitPrice * $itemData['quantity'];
                
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'status' => 'pending',
                    'waiter_id' => null,
                    'special_notes' => $itemData['note'] ?? null,
                ]);

                foreach ($validOptions as $opt) {
                    \App\Models\OrderItemOption::create([
                        'order_item_id' => $orderItem->id,
                        'product_option_id' => $opt->id,
                        'product_option_group_name' => $opt->group->name ?? 'Option',
                        'product_option_name' => $opt->name,
                        'price' => $opt->price_adjustment
                    ]);
                }

                $orderTotal += $lineTotal;
            }

            $order->update(['subtotal' => $orderTotal, 'grand_total' => $orderTotal]);
            $session->increment('total_amount', $orderTotal);

            DB::commit();
            return response()->json(['message' => 'Sipariş Alındı!', 'order_id' => $order->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Hata: ' . $e->getMessage()], 500);
        }
    }
}
