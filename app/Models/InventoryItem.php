<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;


class InventoryItem extends Model
{
    protected $fillable = ['name', 'sku', 'description', 'price'];


    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class);
    }

    /**
     * Scope to search by name
     */
    public function scopeSearchByName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    /**
     * Scope to search by SKU
     */
    public function scopeSearchBySku(Builder $query, string $sku): Builder
    {
        return $query->where('sku', 'like', '%' . $sku . '%');
    }

    /**
     * Scope to filter by price range
     */
    public function scopePriceRange(Builder $query, float $minPrice, float $maxPrice): Builder
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    /**
     * Scope to filter by price greater than
     */
    public function scopeMinPrice(Builder $query, float $price): Builder
    {
        return $query->where('price', '>=', $price);
    }

    /**
     * Scope to filter by price less than
     */
    public function scopeMaxPrice(Builder $query, float $price): Builder
    {
        return $query->where('price', '<=', $price);
    }

    /**
     * Scope to search by name or SKU
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', '%' . $search . '%')
                     ->orWhere('sku', 'like', '%' . $search . '%');
    }
}
