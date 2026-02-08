# Api-Warehouses Architecture Documentation

## Overview

This document describes the architecture and design patterns used in the API-Warehouses application, focusing on the Repository and Service layer patterns implemented.

## Architecture Overview

The application follows a clean, layered architecture:

```
├── Controllers (API endpoints)
│   ├── InventoryItemController
│   ├── WarehouseController
│   ├── StockController
│   └── StockTransferController
│
├── Services (Business Logic)
│   ├── InventoryItemService
│   ├── WarehouseService
│   ├── StockService
│   └── StockTransferService
│
├── Repositories (Data Access)
│   ├── Contracts (Interfaces)
│   │   ├── RepositoryInterface
│   │   ├── InventoryItemRepositoryInterface
│   │   ├── WarehouseRepositoryInterface
│   │   ├── StockRepositoryInterface
│   │   └── StockTransferRepositoryInterface
│   │
│   └── Eloquent (Implementations)
│       ├── BaseRepository
│       ├── InventoryItemRepository
│       ├── WarehouseRepository
│       ├── StockRepository
│       └── StockTransferRepository
│
├── Models (Eloquent Models)
│   ├── InventoryItem
│   ├── Warehouse
│   ├── Stock
│   └── StockTransfer
│
├── Events
│   └── LowStockDetected
│
└── Listeners
    └── SendLowStockAlert
```

## Design Patterns

### 1. Repository Pattern

The Repository Pattern abstracts data access logic and provides a clean interface for the services to use.

**Benefits:**
- Decouples business logic from data access
- Makes it easy to switch databases or implement caching
- Improves testability by allowing mock repositories
- Centralizes query logic

**Components:**

- **Contracts (Interfaces)**: Define the public API that repositories must implement
  - `RepositoryInterface`: Base interface for all repositories
  - `[Entity]RepositoryInterface`: Specific interfaces for each entity

- **Eloquent Implementations**: Concrete implementations using Laravel's Eloquent ORM
  - `BaseRepository`: Shared CRUD operations
  - `[Entity]Repository`: Entity-specific queries

### 2. Service Layer Pattern

Services encapsulate business logic and orchestrate operations between repositories and models.

**Benefits:**
- Keeps business logic out of controllers
- Provides reusable business operations
- Makes it easier to add cross-cutting concerns (caching, logging, etc.)
- Improves code testability

## Dependency Injection

The application uses Laravel's Service Container for dependency injection.

### Service Provider

`RepositoryServiceProvider` handles registration of all repositories and services:

```php
// Register Repository Interface with Implementation
$this->app->bind(
    InventoryItemRepositoryInterface::class,
    InventoryItemRepository::class
);

// Register Service with Dependencies
$this->app->singleton(InventoryItemService::class, function ($app) {
    return new InventoryItemService(
        $app->make(InventoryItemRepositoryInterface::class)
    );
});
```

## Usage Examples

### Controllers

Controllers receive services via constructor dependency injection:

```php
class InventoryItemController extends Controller
{
    public function __construct(
        private InventoryItemService $inventoryItemService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->inventoryItemService->search(
            $request->get('search'),
            $request->get('per_page', 15)
        );

        return response()->json([
            'status' => 'success',
            'data' => $items->items(),
            'pagination' => [
                'total' => $items->total(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
            ],
        ]);
    }
}
```

### Services

Services encapsulate business logic and use repositories for data access:

```php
class InventoryItemService
{
    public function __construct(
        private InventoryItemRepositoryInterface $inventoryItemRepository
    ) {}

    public function search(string $term, int $perPage = 15): Paginator
    {
        return $this->inventoryItemRepository->search($term, $perPage);
    }

    public function createItem(array $data)
    {
        return $this->inventoryItemRepository->create($data);
    }
}
```

### Repositories

Repositories provide a clean interface for data access:

```php
class InventoryItemRepository extends BaseRepository implements InventoryItemRepositoryInterface
{
    protected function getModel(): string
    {
        return InventoryItem::class;
    }

    public function search(string $term, int $perPage = 15)
    {
        return $this->model
            ->search($term)
            ->paginate($perPage);
    }
}
```

## Entity Operations

### InventoryItemService

```php
// Search operations
$service->search($term, $perPage = 15)              // General search
$service->searchByName($name, $perPage = 15)        // Search by name
$service->searchBySku($sku, $perPage = 15)          // Search by SKU
$service->filterByPriceRange($min, $max, $perPage)  // Filter by price
$service->getWithStock($perPage = 15)               // Get with stock info

// CRUD operations
$service->getAllItems($perPage = 15)                // Get all items
$service->getItem($id)                              // Get single item
$service->createItem($data)                         // Create item
$service->updateItem($id, $data)                    // Update item
$service->deleteItem($id)                           // Delete item
```

### WarehouseService

```php
// Warehouse operations
$service->getAllWarehouses($perPage = 15)           // Get all warehouses
$service->getAllWarehousesWithInventory()           // Get all with inventory
$service->getWarehouse($id)                         // Get single warehouse
$service->getWarehouseWithInventory($id)            // Get with inventory details
$service->getWarehouseInventory($id, $perPage = 15) // Get inventory paginated

// CRUD operations
$service->createWarehouse($data)                    // Create warehouse
$service->updateWarehouse($id, $data)               // Update warehouse
$service->deleteWarehouse($id)                      // Delete warehouse
```

### StockService

```php
// Stock operations
$service->getAllStocks($perPage, $warehouseId, $itemId)  // Get all stocks
$service->getStock($id)                                  // Get single stock
$service->getStockByWarehouseAndItem($warehouseId, $itemId)  // Get specific stock
$service->getLowStockItems($threshold = 10, $perPage)    // Get low stock items

// Stock management
$service->recordStock($warehouseId, $itemId, $quantity)  // Record new/update stock
$service->updateStockQuantity($id, $quantity)            // Update quantity
$service->adjustStock($id, $delta)                       // Adjust by delta
$service->deleteStock($id)                               // Delete stock
```

### StockTransferService

```php
// Transfer operations
$service->getAllTransfers($perPage, $warehouseId)   // Get all transfers
$service->getTransfer($id)                          // Get single transfer
$service->getItemTransferHistory($itemId, $perPage) // Get item history
$service->transferStock($fromId, $toId, $itemId, $qty)  // Execute transfer
```

## Events and Listeners

### LowStockDetected Event

Triggered when stock quantity falls below the threshold (default: 10).

**Flow:**
1. Stock is updated (via service)
2. If quantity < 10, `LowStockDetected` event is dispatched
3. `SendLowStockAlert` listener handles the event
4. Admin notification is logged (or email sent in production)

```php
// Event is automatically triggered
$service->recordStock($warehouseId, $itemId, $quantity);
// If quantity < 10, the event fires

// In production, the listener would send an email:
// Mail::to(config('warehouse.admin_email'))->send(
//     new LowStockNotification($stock, $threshold)
// );
```

## Caching Strategy

The application implements caching at the repository/service level:

### Warehouse Caching

```php
// In WarehouseController
Cache::remember('warehouses.all', 3600, function () {
    return $this->warehouseService->getAllWarehousesWithInventory();
});
```

Cache is invalidated when:
- A warehouse is created
- A warehouse is updated
- A warehouse is deleted

## Database Transactions

Stock transfers use database transactions to ensure data consistency:

```php
public function transferStock($fromId, $toId, $itemId, $quantity)
{
    return DB::transaction(function () use ($fromId, $toId, $itemId, $quantity) {
        // All operations succeed together or all rollback
        $this->validateSource($fromId, $itemId, $quantity);
        $this->deductFromSource($fromId, $itemId, $quantity);
        $this->addToDestination($toId, $itemId, $quantity);
        $this->recordTransfer($fromId, $toId, $itemId, $quantity);
    });
}
```

## Validation

All incoming data is validated using Laravel Form Requests:

```php
// StoreInventoryItemRequest
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'sku' => 'required|string|unique:inventory_items,sku|max:255',
        'description' => 'nullable|string|max:1000',
        'price' => 'required|numeric|min:0|max:999999.99',
    ];
}
```

## Search and Filtering

### InventoryItem Search Scopes

The InventoryItem model provides query scopes used by the repository:

```php
// By name
Model::searchByName($name)
Model::searchBySku($sku)
Model::priceRange($min, $max)
Model::minPrice($min)
Model::maxPrice($max)
Model::search($term)  // Searches name and SKU
```

## Pagination

All list operations support pagination:

```php
// Default: 15 items per page
GET /api/inventory-items?per_page=25&page=2

// Response includes pagination metadata
{
    "status": "success",
    "data": [...],
    "pagination": {
        "total": 100,
        "per_page": 25,
        "current_page": 2,
        "last_page": 4,
        "from": 26,
        "to": 50
    }
}
```

## Error Handling

Services throw exceptions for validation failures, which are caught by controllers:

```php
public function store(TransferStockRequest $request): JsonResponse
{
    try {
        $transfer = $this->transferService->transferStock(...);
        return response()->json([...], 201);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 422);
    }
}
```

## API Endpoints

### Inventory Items

```
GET    /api/inventory-items              List items (search, filter, paginate)
POST   /api/inventory-items              Create item
GET    /api/inventory-items/{id}         Get item
PUT    /api/inventory-items/{id}         Update item
DELETE /api/inventory-items/{id}         Delete item

Query Parameters:
- search: Search by name or SKU
- name: Search by name
- sku: Search by SKU
- min_price: Minimum price
- max_price: Maximum price
- per_page: Items per page (default: 15)
```

### Warehouses

```
GET    /api/warehouses                   List warehouses
POST   /api/warehouses                   Create warehouse
GET    /api/warehouses/{id}              Get warehouse
GET    /api/warehouses/{id}/inventory    Get warehouse inventory
PUT    /api/warehouses/{id}              Update warehouse
DELETE /api/warehouses/{id}              Delete warehouse
```

### Stocks

```
GET    /api/stocks                       List stocks
POST   /api/stocks                       Record stock
GET    /api/stocks/{id}                  Get stock
PUT    /api/stocks/{id}                  Update stock
DELETE /api/stocks/{id}                  Delete stock

Query Parameters:
- warehouse_id: Filter by warehouse
- inventory_item_id: Filter by item
- per_page: Items per page
```

### Stock Transfers

```
GET    /api/stock-transfers              List transfers
POST   /api/stock-transfers              Create transfer
GET    /api/stock-transfers/{id}         Get transfer
GET    /api/stock-transfers/item-history Get item transfer history

Query Parameters:
- warehouse_id: Filter by warehouse
- item_id: Filter by item
- per_page: Items per page
```

## Testing

To test the repository and service layer:

```bash
# Run tests
php artisan test

# Test specific class
php artisan test --filter=InventoryItemServiceTest

# Run with coverage
php artisan test --coverage
```

## Future Enhancements

1. **Caching Strategy**
   - Implement more granular caching strategies
   - Add cache tagging for easier invalidation

2. **API Response Formatting**
   - Create a response formatter for consistent API responses
   - Add response versioning

3. **Audit Logging**
   - Log all changes to inventory and stock
   - Track who made changes and when

4. **Notifications**
   - Implement email notifications for low stock
   - Add SMS notifications for critical items

5. **Advanced Filtering**
   - Add date range filters for transfers
   - Add complex filter combinations

6. **Performance Optimization**
   - Implement query result caching
   - Add database indexing for common queries
