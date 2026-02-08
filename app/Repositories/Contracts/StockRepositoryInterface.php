<?php

namespace App\Repositories\Contracts;

interface StockRepositoryInterface extends RepositoryInterface
{
    /**
     * Get stock by warehouse and item
     */
    public function getByWarehouseAndItem(int $warehouseId, int $itemId);

    /**
     * Get low stock items
     */
    public function getLowStock(int $threshold = 10, int $perPage = 15);

    /**
     * Get all stocks with relationships, optionally filtered
     */
    public function getAllWithRelations(?int $warehouseId = null, ?int $itemId = null, int $perPage = 15);

    /**
     * Update stock quantity
     */
    public function updateQuantity(int $id, int $quantity);

    /**
     * Adjust stock by a delta amount
     */
    public function adjustQuantity(int $id, int $delta);
}
