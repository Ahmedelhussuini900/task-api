<?php

namespace App\Models;

use App\Events\LowStockDetected;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Stock extends Model
{
    protected $fillable = [
        'warehouse_id',
        'inventory_item_id',
        'quantity',
    ];

    protected static function booted(): void
    {
        static::updated(function (Stock $stock) {
            // Trigger LowStockDetected event if quantity falls below threshold
            if ($stock->quantity < 10) {
                LowStockDetected::dispatch($stock, 10);
            }
        });
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
