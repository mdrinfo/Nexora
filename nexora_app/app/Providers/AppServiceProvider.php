<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\InventoryItem;
use App\Observers\InventoryItemObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        InventoryItem::observe(InventoryItemObserver::class);
    }
}
