<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\DailyReport;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SendDailyReport extends Command
{
    protected $signature = 'nexora:daily-report';
    protected $description = 'Send daily end-of-day report to owner';

    public function handle()
    {
        $today = Carbon::today();
        
        // 1. Data Aggregation
        $cashOrders = Order::query()
            ->whereDate('created_at', $today)
            ->where('payment_method', 'cash')
            ->get();
            
        $cashRevenue = $cashOrders->sum('grand_total');

        $totalRevenue = Order::query()
            ->whereDate('created_at', $today)
            ->sum('grand_total');

        $topItems = OrderItem::query()
            ->whereDate('created_at', $today)
            ->select('product_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(line_total) as total_sales'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $waiterPerformance = Order::query()
            ->whereDate('created_at', $today)
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('count(*) as order_count'), DB::raw('sum(grand_total) as total_sales'))
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('total_sales')
            ->get();

        $data = [
            'date' => $today->format('d/m/Y'),
            'cashRevenue' => $cashRevenue,
            'totalRevenue' => $totalRevenue,
            'topItems' => $topItems,
            'waiterPerformance' => $waiterPerformance,
            'cashOrdersCount' => $cashOrders->count(),
        ];

        // 2. Generate CSV
        $csvContent = "Report Date,{$today->format('d/m/Y')}\n";
        $csvContent .= "Total Revenue,{$totalRevenue}\n";
        $csvContent .= "Cash Revenue,{$cashRevenue}\n\n";
        
        $csvContent .= "Top Items\nProduct,Quantity,Sales\n";
        foreach ($topItems as $item) {
            $csvContent .= "\"{$item->product->name}\",{$item->total_qty},{$item->total_sales}\n";
        }
        
        $csvContent .= "\nWaiter Performance\nName,Orders,Sales\n";
        foreach ($waiterPerformance as $wp) {
            $csvContent .= "\"{$wp->user->name}\",{$wp->order_count},{$wp->total_sales}\n";
        }

        $fileName = 'daily_report_' . $today->format('Y-m-d') . '.csv';
        $filePath = 'reports/' . $fileName;
        
        // 3. Upload to Cloud/Local
        Storage::put($filePath, $csvContent);
        
        // 4. Send Email
        // Use a default email if not configured
        $ownerEmail = config('mail.from.address', 'admin@nexora.com');
        
        Mail::to($ownerEmail)->send(new DailyReport($data, $filePath));

        $this->info('Daily report sent successfully.');
        return 0;
    }
}
