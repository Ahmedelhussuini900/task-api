<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLowStockAlert implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * In a real application, this would send an email notification to the admin.
     * For now, we'll just log the event.
     */
    public function handle(LowStockDetected $event): void
    {
        // Log the low stock alert
        \Log::warning('Low stock detected', [
            'warehouse_id' => $event->stock->warehouse_id,
            'inventory_item_id' => $event->stock->inventory_item_id,
            'current_quantity' => $event->stock->quantity,
            'threshold' => $event->threshold,
            'item_name' => $event->stock->item->name,
            'warehouse_name' => $event->stock->warehouse->name,
        ]);

        // In a real application, you would send an email like this:
        // Mail::to(config('warehouse.admin_email'))->send(new LowStockNotification($event->stock, $event->threshold));
    }
}
