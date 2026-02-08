<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class WarehouseController extends Controller
{
    public function __construct(
        private WarehouseService $warehouseService
    ) {}

    /**
     * Display a listing of warehouses with caching.
     */
    public function index(): JsonResponse
    {
        // Cache the warehouses for 1 hour (3600 seconds)
        $warehouses = Cache::remember('warehouses.all', 3600, function () {
            return $this->warehouseService->getAllWarehousesWithInventory();
        });

        return response()->json([
            'status' => 'success',
            'data' => $warehouses,
        ]);
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $warehouse = $this->warehouseService->createWarehouse($request->validated());

        // Clear the cache after creating a new warehouse
        Cache::forget('warehouses.all');

        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse created successfully.',
            'data' => $warehouse,
        ], 201);
    }

    /**
     * Display the specified warehouse with inventory data.
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        // Cache individual warehouse with its inventory
        $cacheKey = 'warehouse.' . $warehouse->id . '.inventory';
        $warehouseData = Cache::remember($cacheKey, 3600, function () use ($warehouse) {
            return $this->warehouseService->getWarehouseWithInventory($warehouse->id);
        });

        return response()->json([
            'status' => 'success',
            'data' => $warehouseData,
        ]);
    }

    /**
     * Display warehouse inventory with pagination.
     */
    public function inventory(Warehouse $warehouse): JsonResponse
    {
        // Get warehouse with paginated stocks
        $stocks = $this->warehouseService->getWarehouseInventory($warehouse->id, 15);

        return response()->json([
            'status' => 'success',
            'data' => [
                'warehouse' => $warehouse->only(['id', 'name', 'location']),
                'stocks' => $stocks->items(),
                'pagination' => [
                    'total' => $stocks->total(),
                    'per_page' => $stocks->perPage(),
                    'current_page' => $stocks->currentPage(),
                    'last_page' => $stocks->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        $updated = $this->warehouseService->updateWarehouse($warehouse->id, $request->validated());

        // Clear the cache after updating a warehouse
        Cache::forget('warehouses.all');
        Cache::forget('warehouse.' . $warehouse->id . '.inventory');

        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse updated successfully.',
            'data' => $updated,
        ]);
    }

    /**
     * Remove the specified warehouse from storage.
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $this->warehouseService->deleteWarehouse($warehouse->id);

        // Clear the cache after deleting a warehouse
        Cache::forget('warehouses.all');
        Cache::forget('warehouse.' . $warehouse->id . '.inventory');

        return response()->json([
            'status' => 'success',
            'message' => 'Warehouse deleted successfully.',
        ]);
    }
}
