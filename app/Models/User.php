<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasPermission(string $permissionKey): bool
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap->permissions
            ->contains(fn ($p) => $p->key === $permissionKey);
    }
}

