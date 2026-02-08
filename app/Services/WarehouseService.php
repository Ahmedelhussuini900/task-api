<?php

namespace App\Services;

use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

class WarehouseService
{
    public function __construct(
        private WarehouseRepositoryInterface $warehouseRepository
    ) {}

    /**
     * Get all warehouses with pagination
     */
    public function getAllWarehouses(int $perPage = 15): Paginator
    {
        return $this->warehouseRepository->paginate($perPage);
    }

    /**
     * Get all warehouses with inventory
     */
    public function getAllWarehousesWithInventory(): Collection
    {
        return $this->warehouseRepository->allWithInventory();
    }

    /**
     * Get a single warehouse
     */
    public function getWarehouse(int $id)
    {
        return $this->warehouseRepository->find($id);
    }

    /**
     * Get warehouse with inventory details
     */
    public function getWarehouseWithInventory(int $id)
    {
        return $this->warehouseRepository->getWithInventory($id);
    }

    /**
     * Create a new warehouse
     */
    public function createWarehouse(array $data)
    {
        return $this->warehouseRepository->create($data);
    }

    /**
     * Update a warehouse
     */
    public function updateWarehouse(int $id, array $data)
    {
        return $this->warehouseRepository->update($id, $data);
    }

    /**
     * Delete a warehouse
     */
    public function deleteWarehouse(int $id): bool
    {
        return $this->warehouseRepository->delete($id);
    }

    /**
     * Get warehouse inventory with pagination
     */
    public function getWarehouseInventory(int $warehouseId, int $perPage = 15): Paginator
    {
        return $this->warehouseRepository->getInventoryByWarehouse($warehouseId, $perPage);
    }
}
