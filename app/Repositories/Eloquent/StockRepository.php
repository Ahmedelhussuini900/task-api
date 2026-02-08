<?php

namespace App\Repositories\Eloquent;

use App\Models\Stock;
use App\Repositories\Contracts\StockRepositoryInterface;

class StockRepository extends BaseRepository implements StockRepositoryInterface
{
    protected function getModel(): string
    {
        return Stock::class;
    }

    public function getByWarehouseAndItem(int $warehouseId, int $itemId)
    {
        return $this->model
            ->where('warehouse_id', $warehouseId)
            ->where('inventory_item_id', $itemId)
            ->with(['warehouse', 'item'])
            ->first();
    }

    public function getLowStock(int $threshold = 10, int $perPage = 15)
    {
        return $this->model
            ->where('quantity', '<', $threshold)
            ->with(['warehouse', 'item'])
            ->paginate($perPage);
    }

    public function getAllWithRelations(?int $warehouseId = null, ?int $itemId = null, int $perPage = 15)
    {
        $query = $this->model->with(['warehouse', 'item']);

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($itemId !== null) {
            $query->where('inventory_item_id', $itemId);
        }

        return $query->paginate($perPage);
    }

    public function updateQuantity(int $id, int $quantity)
    {
        $stock = $this->find($id);
        if ($stock) {
            $stock->update(['quantity' => $quantity]);
            return $stock;
        }
        return null;
    }

    public function adjustQuantity(int $id, int $delta)
    {
        $stock = $this->find($id);
        if ($stock) {
            $stock->update(['quantity' => $stock->quantity + $delta]);
            return $stock;
        }
        return null;
    }
}
