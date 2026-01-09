<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderReady implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load(['session.diningTable']);
    }

    public function broadcastOn(): array
    {
        return ['orders'];
    }

    public function broadcastAs(): string
    {
        return 'order.ready';
    }
}
