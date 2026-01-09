<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use App\Models\Session;
use App\Models\Order;
use App\Models\InventoryItem;
use App\Models\Reservation;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $openSessions = Session::query()->where('status', 'open')->count();
        $ordersToday = Order::query()->whereDate('created_at', $today)->count();
        $revenueToday = Order::query()->whereDate('created_at', $today)->sum('grand_total');
        $lowStockCount = InventoryItem::query()
            ->whereColumn('quantity', '<=', 'min_threshold')
            ->count();
        $upcomingReservations = Reservation::query()
            ->whereDate('event_date', '>=', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $recentOrders = Order::query()
            ->latest('created_at')
            ->limit(10)
            ->with(['items.product', 'session.diningTable'])
            ->get();

        return view('admin.dashboard', [
            'openSessions' => $openSessions,
            'ordersToday' => $ordersToday,
            'revenueToday' => $revenueToday,
            'lowStockCount' => $lowStockCount,
            'upcomingReservations' => $upcomingReservations,
            'recentOrders' => $recentOrders,
        ]);
    }
}

