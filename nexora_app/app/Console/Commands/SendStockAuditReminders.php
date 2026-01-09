<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryItem;
use App\Models\User;
use App\Notifications\StockAuditReminder;
use Carbon\Carbon;

class SendStockAuditReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:audit-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for items that have not been audited in the last 48 hours.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $threshold = Carbon::now()->subDays(2);
        
        $itemsToAuditCount = InventoryItem::query()
            ->where(function($q) use ($threshold) {
                $q->whereNull('last_audited_at')
                  ->orWhere('last_audited_at', '<', $threshold);
            })
            ->count();

        if ($itemsToAuditCount > 0) {
            $this->info("Found {$itemsToAuditCount} items requiring audit.");

            // Find users with role 'manager' or 'chef'
            // Assuming roles are handled via a relationship or helper method
            // Based on previous code, we can filter users. 
            // Since I don't see a direct scope for roles in User model yet, I'll fetch all and filter or use whereHas if roles are relation.
            // Let's assume a simple retrieval for now and filter in PHP if necessary, or check how roles are implemented.
            // In sidebar: $user->hasRoleKey('manager')
            
            $users = User::all()->filter(function ($user) {
                return $user->hasRoleKey('manager') || $user->hasRoleKey('chef') || $user->hasRoleKey('owner');
            });

            if ($users->isEmpty()) {
                $this->warn('No users found with manager/chef/owner role to notify.');
                return 0;
            }

            foreach ($users as $user) {
                $user->notify(new StockAuditReminder($itemsToAuditCount));
                $this->info("Notification sent to {$user->email}");
            }
        } else {
            $this->info('No items require audit at this time.');
        }

        return 0;
    }
}
