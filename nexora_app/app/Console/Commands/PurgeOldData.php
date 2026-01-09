<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Setting;
use App\Models\Reservation;
use App\Models\InventoryTransaction;
use Carbon\Carbon;

class PurgeOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nexora:purge-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge or anonymize old sensitive data based on retention policy';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenant = Tenant::query()->where('slug', 'nexora')->first();
        if (!$tenant) {
            $this->error('Tenant not found.');
            return 1;
        }

        $setting = Setting::query()->where('tenant_id', $tenant->id)->first();
        if (!$setting || !$setting->enable_data_purge) {
            $this->info('Data purge is disabled in settings.');
            return 0;
        }

        $days = $setting->retention_period_days ?? 365;
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Purging data older than {$days} days (Cutoff: {$cutoffDate->toDateString()})...");

        // 1. Anonymize Reservations
        $updatedReservations = Reservation::query()
            ->where('tenant_id', $tenant->id)
            ->where('event_date', '<', $cutoffDate)
            ->where('status', '!=', 'pending') // Only anonymize completed/cancelled events
            ->update([
                'customer_name' => 'Anonymized User',
                'customer_phone' => null,
                'notes' => null,
            ]);

        $this->info("Anonymized {$updatedReservations} old reservations.");

        // 2. Delete old Inventory Transactions (optional optimization)
        // We keep 'audit' logs but maybe delete granular usage logs if needed.
        // For now, let's assume we keep them for reporting unless explicitly asked to delete.
        // But the prompt said "delete or anonymize". Let's just anonymize for now to be safe.
        
        $this->info('Data purge completed successfully.');
        return 0;
    }
}
