<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    protected $guarded = [];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function servedItems()
    {
        return $this->hasMany(OrderItem::class, 'served_by');
    }

    public function hasPermission(string $permissionKey): bool
    {
        $permissions = $this->roles()->with('permissions')->get()->flatMap(function ($role) {
            return $role->permissions;
        });
        foreach ($permissions as $p) {
            if ($p->key === $permissionKey) {
                return true;
            }
        }
        return false;
    }

    public function hasRoleKey(string $key): bool
    {
        foreach ($this->roles as $role) {
            if ($role->key === $key) {
                return true;
            }
        }
        return false;
    }
}
