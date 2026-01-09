<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'admin@nexora.local'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );

        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();
        $owner = Role::query()->where('tenant_id', $tenant->id)->where('key', 'owner')->first();
        if ($owner) {
            $user->roles()->syncWithoutDetaching([$owner->id]);
        }
    }
}

