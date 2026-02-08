<?php

namespace App\Providers;

use App\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Contracts\StockTransferRepositoryInterface;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Repositories\Eloquent\InventoryItemRepository;
use App\Repositories\Eloquent\StockRepository;
use App\Repositories\Eloquent\StockTransferRepository;
use App\Repositories\Eloquent\WarehouseRepository;
use App\Services\InventoryItemService;
use App\Services\StockService;
use App\Services\StockTransferService;
use App\Services\WarehouseService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repository Interfaces with their implementations
        $this->app->bind(
            InventoryItemRepositoryInterface::class,
            InventoryItemRepository::class
        );

        $this->app->bind(
            WarehouseRepositoryInterface::class,
            WarehouseRepository::class
        );

        $this->app->bind(
            StockRepositoryInterface::class,
            StockRepository::class
        );

        $this->app->bind(
            StockTransferRepositoryInterface::class,
            StockTransferRepository::class
        );

        // Register Services
        $this->app->singleton(InventoryItemService::class, function ($app) {
            return new InventoryItemService(
                $app->make(InventoryItemRepositoryInterface::class)
            );
        });

        $this->app->singleton(WarehouseService::class, function ($app) {
            return new WarehouseService(
                $app->make(WarehouseRepositoryInterface::class)
            );
        });

        $this->app->singleton(StockService::class, function ($app) {
            return new StockService(
                $app->make(StockRepositoryInterface::class)
            );
        });

        $this->app->singleton(StockTransferService::class, function ($app) {
            return new StockTransferService(
                $app->make(StockTransferRepositoryInterface::class),
                $app->make(StockRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
