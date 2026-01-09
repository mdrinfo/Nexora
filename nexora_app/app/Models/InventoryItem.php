<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InventoryItem extends Model
{
    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'name',
        'category',
        'unit',
        'quantity',
        'min_threshold',
        'cost_price',
        'image_path',
        'last_audited_at'
    ];

    protected $casts = [
        'last_audited_at' => 'datetime',
        'quantity' => 'decimal:6',
        'min_threshold' => 'decimal:6',
        'cost_price' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_recipe_items')
            ->withPivot(['quantity', 'unit'])
            ->withTimestamps();
    }
    
    public function shoppingListItem(): HasOne
    {
        return $this->hasOne(ShoppingListItem::class);
    }
}
