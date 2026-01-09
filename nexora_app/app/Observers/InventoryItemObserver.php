<?php

namespace App\Observers;

use App\Models\InventoryItem;
use App\Models\ShoppingListItem;

class InventoryItemObserver
{
    /**
     * Handle the InventoryItem "created" event.
     *
     * @param  \App\Models\InventoryItem  $inventoryItem
     * @return void
     */
    public function created(InventoryItem $inventoryItem)
    {
        $this->checkStockLevel($inventoryItem);
    }

    /**
     * Handle the InventoryItem "updated" event.
     *
     * @param  \App\Models\InventoryItem  $inventoryItem
     * @return void
     */
    public function updated(InventoryItem $inventoryItem)
    {
        $this->checkStockLevel($inventoryItem);
    }

    /**
     * Check stock level and add to shopping list if low.
     */
    protected function checkStockLevel(InventoryItem $inventoryItem)
    {
        if ($inventoryItem->quantity <= $inventoryItem->min_threshold) {
            // Target stock is 3x the threshold (safe buffer)
            $targetStock = $inventoryItem->min_threshold > 0 ? $inventoryItem->min_threshold * 3 : 10;
            $needed = max(0, $targetStock - $inventoryItem->quantity);

            ShoppingListItem::updateOrCreate(
                [
                    'inventory_item_id' => $inventoryItem->id,
                    'tenant_id' => $inventoryItem->tenant_id,
                ],
                [
                    'quantity_needed' => $needed,
                    'is_manual' => false,
                    // We don't reset 'is_checked' here to avoid unchecking an item someone is buying
                    // But if it was deleted (completed), updateOrCreate will create a new one.
                    // If it exists, we just update the quantity needed.
                ]
            );
        }
    }
}
