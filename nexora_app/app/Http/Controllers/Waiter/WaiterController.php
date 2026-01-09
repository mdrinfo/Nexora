<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Session;
use App\Models\Order;
use App\Models\QrKey;
use App\Models\DiningTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WaiterController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->whereNull('parent_id') // Only top-level products
            ->with(['category', 'optionGroups.options', 'children.optionGroups.options'])
            ->orderBy('name')
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => optional($p->category)->name,
                    'price' => $p->price,
                    'children' => $p->children->map(function($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'price' => $child->price,
                            'option_groups' => $child->optionGroups->map(function($g) {
                                return [
                                    'id' => $g->id,
                                    'name' => $g->name,
                                    'type' => $g->type,
                                    'is_required' => $g->is_required,
                                    'min_selection' => $g->min_selection,
                                    'max_selection' => $g->max_selection,
                                    'options' => $g->options->map(function($o) {
                                        return [
                                            'id' => $o->id,
                                            'name' => $o->name,
                                            'price_adjustment' => $o->price_adjustment,
                                        ];
                                    })
                                ];
                            })
                        ];
                    }),
                    'option_groups' => $p->optionGroups->map(function($g) {
                        return [
                            'id' => $g->id,
                            'name' => $g->name,
                            'type' => $g->type,
                            'is_required' => $g->is_required,
                            'min_selection' => $g->min_selection,
                            'max_selection' => $g->max_selection,
                            'options' => $g->options->map(function($o) {
                                return [
                                    'id' => $o->id,
                                    'name' => $o->name,
                                    'price_adjustment' => $o->price_adjustment,
                                ];
                            })
                        ];
                    })
                ];
            });
            
        // Get Floors with Tables for Floor Plan
        $floors = \App\Models\Floor::with(['tables' => function($q) {
            $q->with(['sessions' => function($sq) {
                $sq->where('status', 'open');
            }]);
        }])
        ->orderBy('level', 'asc')
        ->get();

        // Calculate floor accessibility logic
        $previousFloorFull = true;
        
        $floors->transform(function ($floor) use (&$previousFloorFull) {
            // A floor is locked if the previous floor is not full
            $floor->is_locked = !$previousFloorFull;

            $totalTables = $floor->tables->count();
            // Count tables that have at least one open session
            $occupiedTables = $floor->tables->filter(function ($table) {
                return $table->sessions->isNotEmpty();
            })->count();

            // A floor is considered full if all tables are occupied, or if it has no tables
            $isFull = ($totalTables > 0 && $totalTables === $occupiedTables) || $totalTables === 0;

            // Update for the next iteration
            // Exception: If current floor is locked, subsequent floors should also be locked (logic handles this naturally as isFull won't matter much if already locked, but let's keep it simple)
            // Actually, if a floor is locked, users can't fill it, so it remains not full.
            
            // However, we simply update previousFloorFull based on current floor's status
            $previousFloorFull = $isFull;

            return $floor;
        });

        // If no floors, get simple tables list as fallback
        $tables = \App\Models\DiningTable::select('id', 'label', 'capacity')->get();
        
        // Active Sessions for Dashboard List
        $activeSessions = Session::with(['diningTable', 'orders.items'])
            ->where('status', 'open')
            ->orderBy('opened_at', 'desc')
            ->get()
            ->map(function($s) {
                return [
                    'id' => $s->id,
                    'table_name' => $s->diningTable->label ?? '?',
                    'total' => $s->total_amount,
                    'time' => $s->opened_at->format('H:i'),
                    'type' => $s->type ?? 'table', // table, online, takeaway
                    'table_id' => $s->dining_table_id,
                    'status_counts' => [
                        'pending' => $s->orders->flatMap->items->where('status', 'pending')->count(),
                        'preparing' => $s->orders->flatMap->items->where('status', 'preparing')->count(),
                        'ready' => $s->orders->flatMap->items->where('status', 'ready')->count(),
                    ]
                ];
            });

        // Return view
        return view('restaurant.pos', compact('products', 'tables', 'floors', 'activeSessions'));
    }

    public function getActiveSessions()
    {
        $sessions = Session::with(['diningTable', 'orders.items'])
            ->where('status', 'open')
            ->orderBy('opened_at', 'desc')
            ->get()
            ->map(function($s) {
                return [
                    'id' => $s->id,
                    'table_name' => $s->diningTable->label ?? '?',
                    'total' => $s->total_amount,
                    'time' => $s->opened_at->format('H:i'),
                    'type' => $s->type ?? 'table',
                    'table_id' => $s->dining_table_id,
                    'status_counts' => [
                        'pending' => $s->orders->flatMap->items->where('status', 'pending')->count(),
                        'preparing' => $s->orders->flatMap->items->where('status', 'preparing')->count(),
                        'ready' => $s->orders->flatMap->items->where('status', 'ready')->count(),
                    ]
                ];
            });
        return response()->json($sessions);
    }



    // Submit a new order
    public function storeOrder(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:dining_tables,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.options' => 'nullable|array',
            'items.*.options.*' => 'exists:product_options,id',
        ]);

        $tableId = $request->table_id;
        $tenantId = Auth::user()->tenant_id ?? 1;

        DB::beginTransaction();
        try {
            // 1. Find or Create Session
            $session = Session::where('dining_table_id', $tableId)
                ->where('status', 'open')
                ->first();

            if (!$session) {
                // We need a valid qr_key_id. For now, grab the first one or create one if missing
                $qrKey = \App\Models\QrKey::firstOrCreate(
                    ['tenant_id' => $tenantId, 'dining_table_id' => $tableId],
                    ['token' => \Illuminate\Support\Str::random(32), 'status' => 'active']
                );

                $session = Session::create([
                    'tenant_id' => $tenantId,
                    'dining_table_id' => $tableId,
                    'qr_key_id' => $qrKey->id,
                    'status' => 'open',
                    'currency' => 'EUR',
                    'opened_at' => now(),
                ]);
            }

            // 2. Create Order
            $order = Order::create([
                'tenant_id' => $tenantId,
                'session_id' => $session->id,
                'user_id' => Auth::id(),
                'status' => 'pending',
                'subtotal' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
            ]);

            $orderTotal = 0;

            // 3. Add Items
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
                    'waiter_id' => Auth::id(),
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

            // 4. Update Order Totals
            $order->update([
                'subtotal' => $orderTotal,
                'grand_total' => $orderTotal,
            ]);

            // 5. Update Session Total
            $session->increment('total_amount', $orderTotal);

            DB::commit();
            return response()->json(['message' => 'Commande envoyée !', 'order_id' => $order->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    // Get Session Details (Bill)
    public function getSessionDetails($tableId)
    {
        $session = Session::where('dining_table_id', $tableId)
            ->where('status', 'open')
            ->with(['orders.items.product', 'orders.items.options', 'orders.user'])
            ->first();

        if (!$session) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active' => true,
            'session_id' => $session->id,
            'total' => $session->total_amount,
            'opened_at' => $session->opened_at,
            'orders' => $session->orders->map(function($o) {
                return [
                    'id' => $o->id,
                    'status' => $o->status,
                    'total' => $o->grand_total,
                    'waiter' => $o->user->name ?? '?',
                    'items' => $o->items->map(function($i) {
                        return [
                            'name' => $i->product->name,
                            'qty' => $i->quantity,
                            'price' => $i->line_total,
                            'options' => $i->options->map(function($opt) {
                                return [
                                    'name' => $opt->product_option_name,
                                    'price' => $opt->price
                                ];
                            })
                        ];
                    })
                ];
            })
        ]);
    }

    // Pay and Close Session
    public function paySession(Request $request, $tableId)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card',
        ]);

        $session = Session::where('dining_table_id', $tableId)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return response()->json(['message' => 'Session introuvable'], 404);
        }

        // Mark all orders as paid
        $session->orders()->update([
            'payment_method' => $request->payment_method,
            'paid_at' => now(),
            // 'status' => 'served' // Should likely stay 'served' or 'completed'
        ]);

        // Close session
        $session->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return response()->json(['message' => 'Paiement validé et session clôturée']);
    }

    public function printBill($sessionId)
    {
        $session = Session::with(['orders.items.product', 'diningTable'])->find($sessionId);

        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        // Logic to generate PDF or send to thermal printer would go here
        // For now, we return the data needed to render a print view on the client side

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant_name' => 'Nexora Restaurant',
                'restaurant_address' => '123 Avenue de la Gastronomie, Paris',
                'restaurant_phone' => '+33 1 23 45 67 89',
                'ticket_id' => str_pad((string)$session->id, 6, '0', STR_PAD_LEFT),
                'table' => $session->diningTable->label,
                'opened_at' => $session->opened_at->format('d/m/Y H:i'),
                'printed_at' => now()->format('d/m/Y H:i'),
                'total' => $session->total_amount,
                'items' => $session->orders->flatMap(function($order) {
                    return $order->items->map(function($item) {
                        return [
                            'name' => $item->product->name,
                            'qty' => $item->quantity,
                            'price' => $item->unit_price,
                            'total' => $item->line_total,
                        ];
                    });
                })
            ]
        ]);
    }

    public function closeSession(Request $request, $sessionId)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,other',
        ]);

        $session = Session::with('orders.items')->find($sessionId);

        if (!$session || $session->status !== 'open') {
            return response()->json(['error' => 'Session invalid'], 404);
        }

        DB::transaction(function () use ($session, $request) {
            foreach ($session->orders as $order) {
                // Mark pending/preparing/ready items as served so they leave KDS
                $order->items()
                    ->whereIn('status', ['pending', 'preparing', 'ready'])
                    ->update(['status' => 'served']);
                
                $order->update([
                    'status' => 'served',
                    'payment_method' => $request->payment_method,
                    'paid_at' => now()
                ]);
            }

            $session->status = 'closed';
            $session->closed_at = now();
            $session->save();

            // Release QR Key
            if ($session->qr_key_id) {
                QrKey::where('id', $session->qr_key_id)->update([
                    'dining_table_id' => null,
                    'status' => 'available',
                    'assigned_at' => null
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Table fermée avec succès']);
    }

    // Get items that are ready to be served
    public function getReadyItems()
    {
        $items = OrderItem::query()
            ->with(['order.session.diningTable', 'product'])
            ->where('status', 'ready')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'name' => $item->product->name
                    ],
                    'order' => [
                        'session' => [
                            'dining_table' => [
                                'label' => $item->order->session->diningTable->label ?? '?'
                            ]
                        ]
                    ],
                    'updated_at_human' => $item->updated_at->diffForHumans()
                ];
            });

        return response()->json($items);
    }

    public function checkQr(Request $request)
    {
        $request->validate(['qr_data' => 'required']);
        $qrData = $request->qr_data;

        // Try to find the token
        $qrKey = QrKey::where('token', $qrData)->first();

        if (!$qrKey) {
             return response()->json(['status' => 'error', 'message' => 'QR Code non reconnu.']);
        }

        if ($qrKey->status === 'active' && $qrKey->dining_table_id) {
            // Find the active session for this table
            $session = Session::where('dining_table_id', $qrKey->dining_table_id)
                ->where('status', 'open')
                ->first();

            if ($session) {
                return response()->json([
                    'status' => 'active_session',
                    'session_id' => $session->id,
                    'table_id' => $session->dining_table_id
                ]);
            } else {
                 // QR is active but no session? Reset.
                 $qrKey->update(['status' => 'available', 'dining_table_id' => null]);
                 return response()->json([
                    'status' => 'available',
                    'qr_token' => $qrKey->token
                ]);
            }
        }

        // Available
        return response()->json([
            'status' => 'available',
            'qr_token' => $qrKey->token
        ]);
    }

    public function assignTable(Request $request)
    {
        $request->validate([
            'qr_token' => 'required',
            'table_id' => 'required|exists:dining_tables,id'
        ]);

        $qrKey = QrKey::where('token', $request->qr_token)->firstOrFail();
        
        // Check if table is already occupied
        $existingSession = Session::where('dining_table_id', $request->table_id)
            ->where('status', 'open')
            ->first();

        if ($existingSession) {
             return response()->json(['status' => 'error', 'message' => 'Cette table est déjà occupée !']);
        }

        DB::transaction(function() use ($qrKey, $request) {
            $qrKey->update([
                'status' => 'active',
                'dining_table_id' => $request->table_id,
                'assigned_at' => now()
            ]);

            Session::create([
                'tenant_id' => $qrKey->tenant_id,
                'dining_table_id' => $request->table_id,
                'qr_key_id' => $qrKey->id,
                'status' => 'open',
                'currency' => 'EUR', // Default
                'opened_at' => now(),
            ]);
        });

        return response()->json(['success' => true]);
    }



    public function markAsServed($itemId)
    {
        $item = OrderItem::findOrFail($itemId);
        
        if ($item->status === 'ready') {
            $item->update(['status' => 'served']);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => 'Item not ready'], 400);
    }

    // Delete an item (only if pending)
    public function deleteItem($id)
    {
        $item = OrderItem::with('order.session')->find($id);
        if (!$item) return response()->json(['error' => 'Item not found'], 404);
        
        if ($item->status !== 'pending') {
             return response()->json(['error' => 'Impossible de supprimer un article en préparation ou servi'], 400);
        }
        
        DB::transaction(function() use ($item) {
            $order = $item->order;
            $lineTotal = $item->line_total;
            
            $item->delete();
            
            $order->subtotal -= $lineTotal;
            $order->grand_total -= $lineTotal;
            $order->save();
            
            $order->session->decrement('total_amount', $lineTotal);
        });
        
        return response()->json(['success' => true]);
    }

    // Get live tracking list (Preparing, Ready, Served recently)
    public function getLiveItems()
    {
        $items = OrderItem::query()
            ->with(['order.session.diningTable', 'product', 'order.user'])
            ->whereHas('order.session', function($q) {
                $q->where('status', 'open');
            })
            ->whereIn('status', ['pending', 'preparing', 'ready', 'served'])
            ->where('updated_at', '>=', now()->subHours(2))
            ->orderBy('updated_at', 'desc')
            ->get();

        $data = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'table_id' => $item->order->session->diningTable->id ?? null,
                'table_name' => $item->order->session->diningTable->label ?? '?',
                'status' => $item->status, // preparing, ready, served
                'updated_at' => $item->updated_at->format('H:i'),
                'waiter_name' => $item->order->user->name ?? 'Unknown',
            ];
        });

        return response()->json($data);
    }
}
