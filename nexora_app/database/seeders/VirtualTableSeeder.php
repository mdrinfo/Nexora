<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiningTable;
use App\Models\QrKey;
use App\Models\Tenant;

class VirtualTableSeeder extends Seeder
{
    public function run()
    {
        $tenant = Tenant::first() ?? Tenant::create(['name' => 'Default Tenant']);

        // Create Online Order Table
        $onlineTable = DiningTable::firstOrCreate(
            ['label' => 'ONLINE'],
            [
                'tenant_id' => $tenant->id,
                'capacity' => 0,
                'location' => 'virtual'
            ]
        );

        // Create Takeaway Order Table
        $takeawayTable = DiningTable::firstOrCreate(
            ['label' => 'TAKEAWAY'],
            [
                'tenant_id' => $tenant->id,
                'capacity' => 0,
                'location' => 'virtual'
            ]
        );

        // Create Fixed QR Keys for them
        QrKey::firstOrCreate(
            ['token' => 'ONLINE_QR_TOKEN'],
            [
                'tenant_id' => $tenant->id,
                'dining_table_id' => $onlineTable->id,
                'status' => 'active', // Always active
                'assigned_at' => now()
            ]
        );

        QrKey::firstOrCreate(
            ['token' => 'TAKEAWAY_QR_TOKEN'],
            [
                'tenant_id' => $tenant->id,
                'dining_table_id' => $takeawayTable->id,
                'status' => 'active', // Always active
                'assigned_at' => now()
            ]
        );
    }
}
