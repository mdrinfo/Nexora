<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\OrderItem;

class OrderItemReady
{
    public OrderItem $item;

    public function __construct(OrderItem $item)
    {
        $this->item = $item;
    }
}

