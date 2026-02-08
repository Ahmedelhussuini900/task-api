<?php

namespace App\Services;

use App\Repositories\Contracts\InventoryItemRepositoryInterface;
use Illuminate\Pagination\Paginator;

class InventoryItemService
{
    public function __construct(
        private InventoryItemRepositoryInterface $inventoryItemRepository
    ) {}

    /**
     * Get all inventory items with pagination
     */
    public function getAllItems(int $perPage = 15): Paginator
    {
        return $this->inventoryItemRepository->paginate($perPage);
    }

    /**
     * Get a single inventory item
     */
    public function getItem(int $id)
    {
        return $this->inventoryItemRepository->find($id);
    }

    /**
     * Create a new inventory item
     */
    public function createItem(array $data)
    {
        return $this->inventoryItemRepository->create($data);
    }

    /**
     * Update an inventory item
     */
    public function updateItem(int $id, array $data)
    {
        return $this->inventoryItemRepository->update($id, $data);
    }

    /**
     * Delete an inventory item
     */
    public function deleteItem(int $id): bool
    {
        return $this->inventoryItemRepository->delete($id);
    }

    /**
     * Search items by name
     */
    public function searchByName(string $name, int $perPage = 15): Paginator
    {
        return $this->inventoryItemRepository->searchByName($name, $perPage);
    }

    /**
     * Search items by SKU
     */
    public function searchBySku(string $sku, int $perPage = 15): Paginator
    {
        return $this->inventoryItemRepository->searchBySku($sku, $perPage);
    }

    /**
     * Filter items by price range
     */
    public function filterByPriceRange(float $minPrice, float $maxPrice, int $perPage = 15): Paginator
    {
        return $this->inventoryItemRepository->filterByPriceRange($minPrice, $maxPrice, $perPage);
    }

    /**
     * General search
     */
    public function search(string $term, int $perPage = 15): Paginator
    {
        return $this->inventoryItemRepository->search($term, $perPage);
    }

    /**
     * Get items with stock information
     */
    public function getWithStock(int $perPage = 15): Paginator
    {
        return $this->inventoryItemRepository->getWithStock($perPage);
    }
}
