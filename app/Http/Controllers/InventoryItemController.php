<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Services\InventoryItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function __construct(
        private InventoryItemService $inventoryItemService
    ) {}

    /**
     * Display a listing of inventory items with search and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);

        // Apply search filters if provided
        if ($request->has('search')) {
            $items = $this->inventoryItemService->search($request->get('search'), $perPage);
        } elseif ($request->has('name')) {
            $items = $this->inventoryItemService->searchByName($request->get('name'), $perPage);
        } elseif ($request->has('sku')) {
            $items = $this->inventoryItemService->searchBySku($request->get('sku'), $perPage);
        } elseif ($request->has('min_price') || $request->has('max_price')) {
            $minPrice = $request->has('min_price') ? floatval($request->get('min_price')) : 0;
            $maxPrice = $request->has('max_price') ? floatval($request->get('max_price')) : PHP_FLOAT_MAX;
            $items = $this->inventoryItemService->filterByPriceRange($minPrice, $maxPrice, $perPage);
        } else {
            $items = $this->inventoryItemService->getAllItems($perPage);
        }

        return response()->json([
            'status' => 'success',
            'data' => $items->items(),
            'pagination' => [
                'total' => $items->total(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created inventory item in storage.
     */
    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        $item = $this->inventoryItemService->createItem($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory item created successfully.',
            'data' => $item,
        ], 201);
    }

    /**
     * Display the specified inventory item.
     */
    public function show(InventoryItem $inventoryItem): JsonResponse
    {
        $item = $this->inventoryItemService->getItem($inventoryItem->id);

        return response()->json([
            'status' => 'success',
            'data' => $item->load('stocks'),
        ]);
    }

    /**
     * Update the specified inventory item in storage.
     */
    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): JsonResponse
    {
        $updated = $this->inventoryItemService->updateItem($inventoryItem->id, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory item updated successfully.',
            'data' => $updated,
        ]);
    }

    /**
     * Remove the specified inventory item from storage.
     */
    public function destroy(InventoryItem $inventoryItem): JsonResponse
    {
        $this->inventoryItemService->deleteItem($inventoryItem->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory item deleted successfully.',
        ]);
    }
}
