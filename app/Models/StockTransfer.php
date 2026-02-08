<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class StockTransfer extends Model
{
    //
      protected $fillable = [
        'inventory_item_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'quantity',
        'transferred_at',
    ];
       protected $casts = [
        'transferred_at' => 'datetime',
    ];

  public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }
}
