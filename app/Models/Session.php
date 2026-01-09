<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $fillable = [
        'tenant_id',
        'dining_table_id',
        'qr_key_id',
        'status',
        'currency',
        'total_amount',
        'opened_at',
        'closed_at',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function qrKey(): BelongsTo
    {
        return $this->belongsTo(QrKey::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
