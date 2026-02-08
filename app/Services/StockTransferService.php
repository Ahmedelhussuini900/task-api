<?php

namespace App\Services;

use App\Models\Stock;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Contracts\StockTransferRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    public function __construct(
        private StockTransferRepositoryInterface $transferRepository,
        private StockRepositoryInterface $stockRepository
    ) {}

    /**
     * Get all transfers with pagination
     */
    public function getAllTransfers(int $perPage = 15, ?int $warehouseId = null)
    {
        return $this->transferRepository->getAllWithRelations($warehouseId, $perPage);
    }

    /**
     * Get a single transfer
     */
    public function getTransfer(int $id)
    {
        return $this->transferRepository->find($id);
    }

    /**
     * Get transfer history for an item
     */
    public function getItemTransferHistory(int $itemId, int $perPage = 15)
    {
        return $this->transferRepository->getByItem($itemId, $perPage);
    }

    /**
     * Transfer stock between warehouses
     */
    public function transferStock(int $fromWarehouseId, int $toWarehouseId, int $itemId, int $quantity)
    {
        return DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $itemId, $quantity) {
            // Get source warehouse stock
            $sourceStock = $this->stockRepository->getByWarehouseAndItem($fromWarehouseId, $itemId);

            // Validate that the source warehouse has enough quantity
            if (!$sourceStock || $sourceStock->quantity < $quantity) {
                throw new \Exception('Insufficient quantity in the source warehouse.');
            }

            // Get or create destination warehouse stock
            $destinationStock = $this->stockRepository->getByWarehouseAndItem($toWarehouseId, $itemId);

            if (!$destinationStock) {
                $destinationStock = $this->stockRepository->create([
                    'warehouse_id' => $toWarehouseId,
                    'inventory_item_id' => $itemId,
                    'quantity' => 0,
                ]);
                $destinationStock->load(['warehouse', 'item']);
            }

            // Update quantities
            $this->stockRepository->updateQuantity($sourceStock->id, $sourceStock->quantity - $quantity);
            $this->stockRepository->updateQuantity($destinationStock->id, $destinationStock->quantity + $quantity);

            // Record the transfer
            $transfer = $this->transferRepository->create([
                'inventory_item_id' => $itemId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'quantity' => $quantity,
                'transferred_at' => now(),
            ]);

            return $transfer->load(['item', 'fromWarehouse', 'toWarehouse']);
        });
    }
}
