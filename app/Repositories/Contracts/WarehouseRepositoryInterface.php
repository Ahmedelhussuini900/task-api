<?php

namespace App\Repositories\Contracts;

interface WarehouseRepositoryInterface extends RepositoryInterface
{
    /**
     * Get warehouse with inventory/stocks
     */
    public function getWithInventory(int $id);

    /**
     * Get all warehouses with inventory
     */
    public function allWithInventory();

    /**
     * Get warehouse inventory by item
     */
    public function getInventoryByWarehouse(int $warehouseId, int $perPage = 15);
}
