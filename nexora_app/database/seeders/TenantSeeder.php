<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Setting;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => 'nexora'],
            ['name' => 'Nexora', 'default_currency' => 'EUR']
        );

        Setting::query()->firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'brand_name' => 'Nexora',
                'logo_path' => 'storage/logos/nexora.png',
                'primary_color_hex' => '#1F2937',
                'secondary_color_hex' => '#4B5563',
                'accent_color_hex' => '#10B981',
                'currency' => 'EUR',
            ]
        );
    }
}

