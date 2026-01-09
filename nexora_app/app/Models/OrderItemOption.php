<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'product_option_id',
        'product_option_group_name',
        'product_option_name',
        'price'
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
