<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiningTable extends Model
{
    protected $fillable = ['tenant_id', 'label', 'capacity', 'location'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function qrKey(): HasOne
    {
        return $this->hasOne(QrKey::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}

