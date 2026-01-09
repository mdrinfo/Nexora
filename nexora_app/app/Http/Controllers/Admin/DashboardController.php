<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Session;
use App\Models\Order;
use App\Models\InventoryItem;
use App\Models\Reservation;
use App\Models\Inspection;
use App\Models\Contract;
use App\Models\InventoryTransaction;
use App\Models\DiningTable;
use App\Models\Floor;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // 1. Basic Operations
        $openSessions = Session::query()->where('status', 'open')->count();
        $ordersToday = Order::query()->whereDate('created_at', $today)->count();
        $lowStockCount = InventoryItem::query()
            ->whereColumn('quantity', '<=', 'min_threshold')
            ->count();
        
        // 2. Revenue Analytics (Restaurant)
        $revenueStats = [
            'daily' => Order::query()->whereDate('created_at', $today)->sum('grand_total'),
            'weekly' => Order::query()->whereDate('created_at', '>=', $startOfWeek)->sum('grand_total'),
            'monthly' => Order::query()->whereDate('created_at', '>=', $startOfMonth)->sum('grand_total'),
        ];

        // 3. Revenue Analytics (Banquet Hall - Contracts)
        // Based on event_date (Revenue Realization)
        $banquetStats = [
             'daily' => Contract::whereHas('reservation', function($q) use ($today) {
                 $q->whereDate('event_date', $today);
             })->sum('amount'),
             'weekly' => Contract::whereHas('reservation', function($q) use ($startOfWeek) {
                 $q->whereDate('event_date', '>=', $startOfWeek);
             })->sum('amount'),
             'monthly' => Contract::whereHas('reservation', function($q) use ($startOfMonth) {
                 $q->whereDate('event_date', '>=', $startOfMonth);
             })->sum('amount'),
        ];

        // 4. Inventory Waste (Loss)
        // quantity * cost_price
        $wasteQuery = InventoryTransaction::query()
            ->where('type', 'waste')
            ->join('inventory_items', 'inventory_transactions.inventory_item_id', '=', 'inventory_items.id');
        
        $wasteStats = [
            'daily' => (clone $wasteQuery)->whereDate('inventory_transactions.created_at', $today)
                ->sum(DB::raw('inventory_transactions.quantity * inventory_items.cost_price')),
             'monthly' => (clone $wasteQuery)->whereDate('inventory_transactions.created_at', '>=', $startOfMonth)
                ->sum(DB::raw('inventory_transactions.quantity * inventory_items.cost_price')),
        ];

        // 5. Peak Hours (Last 30 days)
        /** @var \Illuminate\Database\Connection $connection */
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $hourExpression = $driver === 'sqlite' ? "strftime('%H', created_at)" : 'HOUR(created_at)';

        $peakHours = Order::query()
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw("$hourExpression as hour"), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // 6. Operational Lists
        $upcomingReservations = Reservation::query()
            ->whereDate('event_date', '>=', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $recentOrders = Order::query()
            ->latest('created_at')
            ->limit(5)
            ->with(['items.product', 'items.options', 'session.diningTable'])
            ->get();

        $ordersPreparing = Order::query()
            ->where('status', 'preparing')
            ->latest('created_at')
            ->limit(5)
            ->with(['items.product', 'items.options', 'session.diningTable'])
            ->get();

        $openSessionsList = Session::query()
            ->where('status', 'open')
            ->latest('opened_at')
            ->limit(5)
            ->with(['diningTable'])
            ->get();
        
        $floors = Floor::query()
            ->with(['tables' => function($q) {
                $q->with(['sessions' => function($q2){ $q2->where('status', 'open'); }])
                  ->orderBy('label');
            }])
            ->orderBy('level')
            ->get();

        $tablesWithoutFloor = DiningTable::query()
            ->whereNull('floor_id')
            ->select('id', 'label', 'capacity', 'location', 'floor_id')
            ->with(['sessions' => function($q){ $q->where('status', 'open'); }])
            ->orderBy('label')
            ->get();
            
        // 7. Top Selling Products (Monthly)
        $topSellingProducts = \App\Models\OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.created_at', '>=', $startOfMonth)
            ->select('products.name', DB::raw('sum(order_items.quantity) as total_qty'))
            ->groupBy('products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 8. Waiter Performance (Monthly)
        $waiterPerformance = \App\Models\User::query()
            ->whereHas('roles', function($q) { $q->where('key', 'waiter'); })
            ->withCount(['orders as orders_taken_count' => function($q) use ($startOfMonth) {
                $q->where('created_at', '>=', $startOfMonth);
            }])
            ->withCount(['servedItems as items_served_count' => function($q) use ($startOfMonth) {
                $q->where('served_at', '>=', $startOfMonth);
            }])
            ->get();

        // 9. Table Occupancy (Average Minutes per Session - Monthly)
        $avgOccupancy = Session::query()
            ->where('status', 'closed')
            ->where('created_at', '>=', $startOfMonth)
            ->get()
            ->avg(function($session) {
                return $session->closed_at && $session->opened_at 
                    ? $session->closed_at->diffInMinutes($session->opened_at) 
                    : 0;
            });
            
        $auditThreshold = Carbon::now()->subDays(2);
        $itemsToAuditCount = InventoryItem::query()
            ->where(function ($query) use ($auditThreshold) {
                $query->whereNull('last_audited_at')
                      ->orWhere('last_audited_at', '<', $auditThreshold);
            })
            ->count();

        // 10. Order Type Distribution (Today)
        $orderTypeStats = Session::query()
            ->join('orders', 'sessions.id', '=', 'orders.session_id')
            ->whereDate('orders.created_at', $today)
            ->select('sessions.type', DB::raw('count(*) as count'), DB::raw('sum(orders.grand_total) as total'))
            ->groupBy('sessions.type')
            ->get()
            ->keyBy('type');

        return view('admin.dashboard', [
            'openSessions' => $openSessions,
            'ordersToday' => $ordersToday,
            'lowStockCount' => $lowStockCount,
            'itemsToAuditCount' => $itemsToAuditCount,
            'upcomingReservations' => $upcomingReservations,
            'recentOrders' => $recentOrders,
            'ordersPreparing' => $ordersPreparing,
            'openSessionsList' => $openSessionsList,
            
            // New Analytics
            'revenueStats' => $revenueStats,
            'banquetStats' => $banquetStats,
            'wasteStats' => $wasteStats,
            'peakHours' => $peakHours,
            'floors' => $floors,
            'tablesWithoutFloor' => $tablesWithoutFloor,
            'topSellingProducts' => $topSellingProducts,
            'waiterPerformance' => $waiterPerformance,
            'avgOccupancy' => $avgOccupancy,
            'orderTypeStats' => $orderTypeStats,
        ]);
    }
}
