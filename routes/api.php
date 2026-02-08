<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\AuthController;

// Public auth routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    // Authenticated user endpoints
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::apiResource('inventory-items', InventoryItemController::class);
    Route::apiResource('warehouses', WarehouseController::class);
    Route::get('warehouses/{warehouse}/inventory', [WarehouseController::class, 'inventory']);
    Route::apiResource('stocks', StockController::class);
    Route::apiResource('stock-transfers', StockTransferController::class)->only(['index', 'store', 'show']);
    Route::get('stock-transfers/item-history', [StockTransferController::class, 'itemHistory']);
});
