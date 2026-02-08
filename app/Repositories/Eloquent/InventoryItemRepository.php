<?php

namespace App\Repositories\Eloquent;

use App\Models\InventoryItem;
use App\Repositories\Contracts\InventoryItemRepositoryInterface;

class InventoryItemRepository extends BaseRepository implements InventoryItemRepositoryInterface
{
    protected function getModel(): string
    {
        return InventoryItem::class;
    }

    public function searchByName(string $name, int $perPage = 15)
    {
        return $this->model
            ->searchByName($name)
            ->paginate($perPage);
    }

    public function searchBySku(string $sku, int $perPage = 15)
    {
        return $this->model
            ->searchBySku($sku)
            ->paginate($perPage);
    }

    public function filterByPriceRange(float $minPrice, float $maxPrice, int $perPage = 15)
    {
        return $this->model
            ->priceRange($minPrice, $maxPrice)
            ->paginate($perPage);
    }

    public function search(string $term, int $perPage = 15)
    {
        return $this->model
            ->search($term)
            ->paginate($perPage);
    }

    public function getWithStock(int $perPage = 15)
    {
        return $this->model
            ->with('stocks')
            ->paginate($perPage);
    }
}
