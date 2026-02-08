<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferStockRequest;
use App\Models\StockTransfer;
use App\Services\StockTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    public function __construct(
        private StockTransferService $transferService
    ) {}

    /**
     * Display a listing of stock transfers.
     */
    public function index(Request $request): JsonResponse
    {
        $warehouseId = $request->get('warehouse_id');
        $perPage = $request->get('per_page', 15);

        $transfers = $this->transferService->getAllTransfers($perPage, $warehouseId);

        return response()->json([
            'status' => 'success',
            'data' => $transfers->items(),
            'pagination' => [
                'total' => $transfers->total(),
                'per_page' => $transfers->perPage(),
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created stock transfer.
     */
    public function store(TransferStockRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $transfer = $this->transferService->transferStock(
                $validated['from_warehouse_id'],
                $validated['to_warehouse_id'],
                $validated['inventory_item_id'],
                $validated['quantity']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Stock transfer completed successfully.',
                'data' => $transfer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified stock transfer.
     */
    public function show(StockTransfer $stockTransfer): JsonResponse
    {
        $transfer = $this->transferService->getTransfer($stockTransfer->id);

        return response()->json([
            'status' => 'success',
            'data' => $transfer->load(['item', 'fromWarehouse', 'toWarehouse']),
        ]);
    }

    /**
     * Get transfer history for a specific item.
     */
    public function itemHistory(Request $request): JsonResponse
    {
        $itemId = $request->get('item_id');
        $perPage = $request->get('per_page', 15);

        if (!$itemId) {
            return response()->json([
                'status' => 'error',
                'message' => 'item_id parameter is required.',
            ], 422);
        }

        $transfers = $this->transferService->getItemTransferHistory($itemId, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $transfers->items(),
            'pagination' => [
                'total' => $transfers->total(),
                'per_page' => $transfers->perPage(),
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
            ],
        ]);
    }
}
