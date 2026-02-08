<?php

namespace App\Repositories\Contracts;

interface InventoryItemRepositoryInterface extends RepositoryInterface
{
    /**
     * Search inventory items by name
     */
    public function searchByName(string $name, int $perPage = 15);

    /**
     * Search inventory items by SKU
     */
    public function searchBySku(string $sku, int $perPage = 15);

    /**
     * Filter by price range
     */
    public function filterByPriceRange(float $minPrice, float $maxPrice, int $perPage = 15);

    /**
     * General search by name or SKU
     */
    public function search(string $term, int $perPage = 15);

    /**
     * Get available inventory with stock information
     */
    public function getWithStock(int $perPage = 15);
}
