<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use App\Events\OrderItemReady;
use App\Models\Notification;

class CreateNotifyWaiter implements ShouldQueue
{
    public function handle(OrderItemReady $event): void
    {
        $item = $event->item->load(['order.session.diningTable', 'product']);
        $waiterId = $item->waiter_id;
        if (!$waiterId) {
            return;
        }
        Notification::query()->create([
            'user_id' => $waiterId,
            'session_id' => optional($item->order)->session_id,
            'order_item_id' => $item->id,
            'message' => 'Prêt: ' . (optional($item->product)->name ?? 'Item') . ' @ ' . (optional(optional($item->order)->session->diningTable)->label ?? 'Table'),
        ]);
        $item->waiter_notified_at = Carbon::now();
        $item->save();
    }
}

