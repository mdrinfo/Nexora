<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\DiningTable;
use App\Models\QrKey;
use App\Models\Session;

class DiningSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();

        $tables = [
            ['label' => 'T1', 'capacity' => 4, 'location' => 'Zone A'],
            ['label' => 'T2', 'capacity' => 4, 'location' => 'Zone A'],
        ];

        foreach ($tables as $t) {
            DiningTable::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'label' => $t['label']],
                ['capacity' => $t['capacity'], 'location' => $t['location']]
            );
        }

        $t1 = DiningTable::query()->where('tenant_id', $tenant->id)->where('label', 'T1')->firstOrFail();
        $t2 = DiningTable::query()->where('tenant_id', $tenant->id)->where('label', 'T2')->firstOrFail();

        $qr1 = QrKey::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'token' => 'qr-T1-0001'],
            ['dining_table_id' => $t1->id, 'status' => 'active', 'assigned_at' => now()]
        );

        QrKey::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'token' => 'qr-T2-0001'],
            ['dining_table_id' => $t2->id, 'status' => 'available', 'assigned_at' => null]
        );

        $session = Session::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'dining_table_id' => $t1->id,
                'qr_key_id' => $qr1->id,
                'status' => 'open',
            ],
            [
                'currency' => 'EUR',
                'total_amount' => 0,
                'opened_at' => now(),
                'closed_at' => null,
            ]
        );

        $service = new \App\Services\OrderService();
        $service->place($session, null, [
            ['product_id' => \App\Models\Product::query()->where('name', 'Pizza Margherita')->firstOrFail()->id, 'quantity' => 2],
        ]);
    }
}
