<?php

namespace App\Repositories\Eloquent;

use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;

class WarehouseRepository extends BaseRepository implements WarehouseRepositoryInterface
{
    protected function getModel(): string
    {
        return Warehouse::class;
    }

    public function getWithInventory(int $id)
    {
        return $this->model
            ->with(['stocks.item'])
            ->find($id);
    }

    public function allWithInventory()
    {
        return $this->model
            ->with('stocks')
            ->get();
    }

    public function getInventoryByWarehouse(int $warehouseId, int $perPage = 15)
    {
        return $this->model
            ->find($warehouseId)
            ->stocks()
            ->with('item')
            ->paginate($perPage);
    }
}
