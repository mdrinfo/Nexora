<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Reservation;
use App\Models\Contract;
use App\Models\Inspection;
use App\Models\InspectionImage;

class BanquetSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->firstOrFail();

        $reservation = Reservation::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'customer_name' => 'Jean Dupont',
                'event_date' => now()->addDays(14)->toDateString(),
            ],
            [
                'customer_phone' => '+33 6 12 34 56 78',
                'start_time' => '18:00:00',
                'end_time' => '23:00:00',
                'guest_count' => 100,
                'hall_name' => 'Salle de Réception',
                'status' => 'confirmed',
                'notes' => 'Menu classique, boissons incluses',
            ]
        );

        $contract = Contract::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'reservation_id' => $reservation->id,
                'contract_number' => 'CTR-0001',
            ],
            [
                'signed_at' => now(),
                'amount' => 1500.00,
                'terms' => 'Contrat de Location standard',
            ]
        );

        $inspection = Inspection::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'reservation_id' => $reservation->id,
                'status' => 'pre_event',
            ],
            [
                'inspected_at' => now(),
                'notes' => 'Etat de lieu avant événement',
            ]
        );

        InspectionImage::query()->firstOrCreate(
            [
                'inspection_id' => $inspection->id,
                'image_path' => 'storage/inspections/pre_event_1.jpg',
            ]
        );
    }
}

