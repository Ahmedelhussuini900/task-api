<?php

namespace App\Repositories\Contracts;

interface StockTransferRepositoryInterface extends RepositoryInterface
{
    /**
     * Get transfers involving a specific warehouse
     */
    public function getByWarehouse(int $warehouseId, int $perPage = 15);

    /**
     * Get transfer history for a specific item
     */
    public function getByItem(int $itemId, int $perPage = 15);

    /**
     * Get all transfers with full relationships
     */
    public function getAllWithRelations(?int $warehouseId = null, int $perPage = 15);
}
