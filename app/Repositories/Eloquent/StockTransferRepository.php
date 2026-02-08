<?php

namespace App\Repositories\Eloquent;

use App\Models\StockTransfer;
use App\Repositories\Contracts\StockTransferRepositoryInterface;

class StockTransferRepository extends BaseRepository implements StockTransferRepositoryInterface
{
    protected function getModel(): string
    {
        return StockTransfer::class;
    }

    public function getByWarehouse(int $warehouseId, int $perPage = 5)
    {
        return $this->model
            ->where(function ($query) use ($warehouseId) {
                $query->where('from_warehouse_id', $warehouseId)
                      ->orWhere('to_warehouse_id', $warehouseId);
            })
            ->with(['item', 'fromWarehouse', 'toWarehouse'])
            ->paginate($perPage);
    }

    public function getByItem(int $itemId, int $perPage = 15)
    {
        return $this->model
            ->where('inventory_item_id', $itemId)
            ->with(['fromWarehouse', 'toWarehouse'])
            ->orderBy('transferred_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllWithRelations(?int $warehouseId = null, int $perPage = 15)
    {
        $query = $this->model->with(['item', 'fromWarehouse', 'toWarehouse']);

        if ($warehouseId !== null) {
            $query->where(function ($q) use ($warehouseId) {
                $q->where('from_warehouse_id', $warehouseId)
                  ->orWhere('to_warehouse_id', $warehouseId);
            });
        }

        return $query->orderBy('transferred_at', 'desc')->paginate($perPage);
    }
}
