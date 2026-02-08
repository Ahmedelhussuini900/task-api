<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Models\Stock;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StockController extends Controller
{
    public function __construct(
        private StockService $stockService
    ) {}

    /**
     * Display a listing of stock records.
     */
    public function index(Request $request): JsonResponse
    {
        $warehouseId = $request->get('warehouse_id');
        $itemId = $request->get('inventory_item_id');
        $perPage = $request->get('per_page', 15);

        $stocks = $this->stockService->getAllStocks($perPage, $warehouseId, $itemId);

        return response()->json([
            'status' => 'success',
            'data' => $stocks->items(),
            'pagination' => [
                'total' => $stocks->total(),
                'per_page' => $stocks->perPage(),
                'current_page' => $stocks->currentPage(),
                'last_page' => $stocks->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created stock in storage.
     */
    public function store(StoreStockRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $stock = $this->stockService->recordStock(
                $validated['warehouse_id'],
                $validated['inventory_item_id'],
                $validated['quantity']
            );

            // Clear cache
            Cache::forget('stocks.all');

            return response()->json([
                'status' => 'success',
                'message' => 'Stock recorded successfully.',
                'data' => $stock,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified stock record.
     */
    public function show(Stock $stock): JsonResponse
    {
        $stockData = $this->stockService->getStock($stock->id);

        return response()->json([
            'status' => 'success',
            'data' => $stockData->load(['warehouse', 'item']),
        ]);
    }

    /**
     * Update the specified stock in storage.
     */
    public function update(UpdateStockRequest $request, Stock $stock): JsonResponse
    {
        try {
            $validated = $request->validated();

            if (isset($validated['quantity'])) {
                $updated = $this->stockService->updateStockQuantity($stock->id, $validated['quantity']);
            } else {
                // If only warehouse_id or inventory_item_id is being updated
                $updated = $this->stockService->updateStock($stock->id, $validated);
            }

            // Clear cache
            Cache::forget('stocks.all');

            return response()->json([
                'status' => 'success',
                'message' => 'Stock updated successfully.',
                'data' => $updated->load(['warehouse', 'item']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified stock from storage.
     */
    public function destroy(Stock $stock): JsonResponse
    {
        $this->stockService->deleteStock($stock->id);

        // Clear cache
        Cache::forget('stocks.all');

        return response()->json([
            'status' => 'success',
            'message' => 'Stock record deleted successfully.',
        ]);
    }

    /**
     * Get low stock items
     */
    public function lowStock(Request $request): JsonResponse
    {
        $threshold = $request->get('threshold', 10);
        $perPage = $request->get('per_page', 15);

        $lowStocks = $this->stockService->getLowStockItems($threshold, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $lowStocks->items(),
            'pagination' => [
                'total' => $lowStocks->total(),
                'per_page' => $lowStocks->perPage(),
                'current_page' => $lowStocks->currentPage(),
                'last_page' => $lowStocks->lastPage(),
            ],
        ]);
    }
}
