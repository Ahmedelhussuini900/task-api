<?php

namespace App\Services;

use App\Events\LowStockDetected;
use App\Repositories\Contracts\StockRepositoryInterface;
use Illuminate\Pagination\Paginator;

class StockService
{
    public function __construct(
        private StockRepositoryInterface $stockRepository
    ) {}

    /**
     * Get all stocks with pagination
     */
    public function getAllStocks(int $perPage = 15, ?int $warehouseId = null, ?int $itemId = null): Paginator
    {
        return $this->stockRepository->getAllWithRelations($warehouseId, $itemId, $perPage);
    }

    /**
     * Get a single stock
     */
    public function getStock(int $id)
    {
        return $this->stockRepository->find($id);
    }

    /**
     * Get stock by warehouse and inventory item
     */
    public function getStockByWarehouseAndItem(int $warehouseId, int $itemId)
    {
        return $this->stockRepository->getByWarehouseAndItem($warehouseId, $itemId);
    }

    /**
     * Record new stock or update existing
     */
    public function recordStock(int $warehouseId, int $itemId, int $quantity)
    {
        $stock = $this->stockRepository->getByWarehouseAndItem($warehouseId, $itemId);

        if ($stock) {
            // Update existing stock
            $stock = $this->stockRepository->updateQuantity($stock->id, $stock->quantity + $quantity);
        } else {
            // Create new stock
            $stock = $this->stockRepository->create([
                'warehouse_id' => $warehouseId,
                'inventory_item_id' => $itemId,
                'quantity' => $quantity,
            ]);
            $stock->load(['warehouse', 'item']);
        }

        // Check if stock is low and trigger event
        if ($stock->quantity < 10) {
            LowStockDetected::dispatch($stock, 10);
        }

        return $stock;
    }

    /**
     * Update stock quantity
     */
    public function updateStockQuantity(int $id, int $quantity)
    {
        $stock = $this->stockRepository->find($id);
        $oldQuantity = $stock->quantity ?? 0;

        $updated = $this->stockRepository->updateQuantity($id, $quantity);

        // Check if stock just became low
        if ($updated && $updated->quantity < 10 && $oldQuantity >= 10) {
            LowStockDetected::dispatch($updated, 10);
        }

        return $updated;
    }

    /**
     * Update stock record (generic update)
     */
    public function updateStock(int $id, array $data)
    {
        return $this->stockRepository->update($id, $data);
    }

    /**
     * Adjust stock by a delta amount
     */
    public function adjustStock(int $id, int $delta)
    {
        $stock = $this->stockRepository->find($id);
        $oldQuantity = $stock->quantity ?? 0;

        $updated = $this->stockRepository->adjustQuantity($id, $delta);

        // Check if stock just became low
        if ($updated && $updated->quantity < 10 && $oldQuantity >= 10) {
            LowStockDetected::dispatch($updated, 10);
        }

        return $updated;
    }

    /**
     * Delete stock
     */
    public function deleteStock(int $id): bool
    {
        return $this->stockRepository->delete($id);
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems(int $threshold = 10, int $perPage = 15): Paginator
    {
        return $this->stockRepository->getLowStock($threshold, $perPage);
    }
}
