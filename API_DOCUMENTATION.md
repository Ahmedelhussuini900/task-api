# Warehouse Inventory API Documentation

This Laravel API provides comprehensive warehouse inventory management with advanced search, pagination, caching, and event-driven notifications.

## Features Implemented

### 1. Data Structures & Algorithms

#### Search Functionality (InventoryItem)
- **Search by Name**: `GET /api/inventory-items?name=Laptop`
- **Search by SKU**: `GET /api/inventory-items?sku=LAPTOP-001`
- **Combined Search**: `GET /api/inventory-items?search=keyboard` (searches both name and SKU)
- **Price Range Filter**: `GET /api/inventory-items?min_price=100&max_price=500`
- **Min Price Filter**: `GET /api/inventory-items?min_price=200`
- **Max Price Filter**: `GET /api/inventory-items?max_price=300`

All search queries support pagination with `per_page` parameter (default: 15).

#### Efficient Pagination
All list endpoints support pagination with the following parameters:
- `page`: Current page number (default: 1)
- `per_page`: Items per page (default: 15)

Example: `GET /api/inventory-items?page=2&per_page=50`

### 2. Laravel Features

#### Eloquent Models
All models use Eloquent ORM for database interactions:
- `InventoryItem`: Manages inventory items with search scopes
- `Warehouse`: Manages warehouse locations
- `Stock`: Manages inventory quantity per warehouse
- `StockTransfer`: Records stock transfers between warehouses
- `User`: User authentication model

#### Validation
Form Requests validate all input data:
- **StoreInventoryItemRequest**: Validates item creation
- **UpdateInventoryItemRequest**: Validates item updates
- **StoreWarehouseRequest**: Validates warehouse creation
- **UpdateWarehouseRequest**: Validates warehouse updates
- **StoreStockRequest**: Validates stock creation with existence checks
- **UpdateStockRequest**: Validates stock updates
- **TransferStockRequest**: Validates stock transfers with warehouse validation

#### Caching
Warehouse inventory endpoints use caching to optimize performance:
- Warehouses list cached for 1 hour
- Individual warehouse inventory cached for 1 hour
- Cache is automatically invalidated on create/update/delete operations

#### Events and Listeners
**LowStockDetected Event**:
- Triggered when stock quantity falls below 10 units
- Automatically dispatched in:
  - `StockController::store()` when creating stock
  - `StockController::update()` when updating stock
  - `Stock::booted()` hook when quantity drops below threshold

**SendLowStockAlert Listener**:
- Listens for `LowStockDetected` events
- Logs low stock warnings with warehouse and item details
- Ready for email notification implementation (use `Mail::to()` for actual emails)

## API Endpoints

### Inventory Items

#### List Inventory Items (with Search & Pagination)
```
GET /api/inventory-items
Query Parameters:
  - page: int (default: 1)
  - per_page: int (default: 15)
  - search: string (searches name and SKU)
  - name: string (exact field search)
  - sku: string (exact field search)
  - min_price: decimal
  - max_price: decimal

Response:
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Laptop",
      "sku": "LAPTOP-001",
      "description": "High-performance laptop",
      "price": "1299.99",
      "created_at": "2026-02-08T10:00:00Z",
      "updated_at": "2026-02-08T10:00:00Z"
    }
  ],
  "pagination": {
    "total": 50,
    "per_page": 15,
    "current_page": 1,
    "last_page": 4,
    "from": 1,
    "to": 15
  }
}
```

#### Create Inventory Item
```
POST /api/inventory-items
Content-Type: application/json

{
  "name": "Laptop",
  "sku": "LAPTOP-001",
  "description": "High-performance laptop",
  "price": 1299.99
}

Response (201):
{
  "status": "success",
  "message": "Inventory item created successfully.",
  "data": {
    "id": 1,
    "name": "Laptop",
    "sku": "LAPTOP-001",
    "price": "1299.99",
    "created_at": "2026-02-08T10:00:00Z"
  }
}
```

#### Retrieve Inventory Item
```
GET /api/inventory-items/{id}

Response:
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Laptop",
    "sku": "LAPTOP-001",
    "price": "1299.99",
    "stocks": [
      {
        "id": 1,
        "warehouse_id": 1,
        "quantity": 25,
        "created_at": "2026-02-08T10:00:00Z"
      }
    ]
  }
}
```

#### Update Inventory Item
```
PATCH /api/inventory-items/{id}
Content-Type: application/json

{
  "name": "Updated Laptop Name",
  "price": 1399.99
}

Response:
{
  "status": "success",
  "message": "Inventory item updated successfully.",
  "data": {
    "id": 1,
    "name": "Updated Laptop Name",
    "price": "1399.99"
  }
}
```

#### Delete Inventory Item
```
DELETE /api/inventory-items/{id}

Response:
{
  "status": "success",
  "message": "Inventory item deleted successfully."
}
```

### Warehouses

#### List Warehouses (Cached)
```
GET /api/warehouses

Response:
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Main Warehouse",
      "location": "New York",
      "created_at": "2026-02-08T10:00:00Z",
      "stocks": [...]
    }
  ]
}
```

#### Create Warehouse
```
POST /api/warehouses
Content-Type: application/json

{
  "name": "Main Warehouse",
  "location": "New York"
}

Response (201):
{
  "status": "success",
  "message": "Warehouse created successfully.",
  "data": {
    "id": 1,
    "name": "Main Warehouse",
    "location": "New York"
  }
}
```

#### Get Warehouse Inventory
```
GET /api/warehouses/{id}/inventory

Response:
{
  "status": "success",
  "data": {
    "warehouse": {
      "id": 1,
      "name": "Main Warehouse",
      "location": "New York"
    },
    "stocks": [
      {
        "id": 1,
        "warehouse_id": 1,
        "inventory_item_id": 1,
        "quantity": 25
      }
    ]
  }
}
```

#### Update Warehouse
```
PATCH /api/warehouses/{id}
Content-Type: application/json

{
  "name": "Updated Warehouse Name",
  "location": "Updated Location"
}

Response:
{
  "status": "success",
  "message": "Warehouse updated successfully.",
  "data": {
    "id": 1,
    "name": "Updated Warehouse Name"
  }
}
```

#### Delete Warehouse
```
DELETE /api/warehouses/{id}

Response:
{
  "status": "success",
  "message": "Warehouse deleted successfully."
}
```

### Stock Management

#### List Stock Records
```
GET /api/stocks
Query Parameters:
  - page: int (default: 1)
  - per_page: int (default: 15)
  - warehouse_id: int (filter by warehouse)
  - inventory_item_id: int (filter by item)

Response:
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "warehouse_id": 1,
      "inventory_item_id": 1,
      "quantity": 25,
      "warehouse": {...},
      "item": {...}
    }
  ],
  "pagination": {...}
}
```

#### Add/Update Stock
```
POST /api/stocks
Content-Type: application/json

{
  "warehouse_id": 1,
  "inventory_item_id": 1,
  "quantity": 25
}

Response (201):
{
  "status": "success",
  "message": "Stock recorded successfully.",
  "data": {
    "id": 1,
    "warehouse_id": 1,
    "inventory_item_id": 1,
    "quantity": 25
  }
}

Note: If stock already exists for the warehouse and item, the quantity will be added.
If quantity drops below 10, LowStockDetected event is triggered.
```

#### Update Stock
```
PATCH /api/stocks/{id}
Content-Type: application/json

{
  "quantity": 50
}

Response:
{
  "status": "success",
  "message": "Stock updated successfully.",
  "data": {
    "id": 1,
    "warehouse_id": 1,
    "quantity": 50
  }
}
```

#### Delete Stock
```
DELETE /api/stocks/{id}

Response:
{
  "status": "success",
  "message": "Stock record deleted successfully."
}
```

### Stock Transfers

#### List Stock Transfers
```
GET /api/stock-transfers
Query Parameters:
  - page: int (default: 1)
  - per_page: int (default: 15)
  - warehouse_id: int (filter by warehouse involvement)

Response:
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "inventory_item_id": 1,
      "from_warehouse_id": 1,
      "to_warehouse_id": 2,
      "quantity": 10,
      "transferred_at": "2026-02-08T10:00:00Z"
    }
  ],
  "pagination": {...}
}
```

#### Transfer Stock Between Warehouses
```
POST /api/stock-transfers
Content-Type: application/json

{
  "inventory_item_id": 1,
  "from_warehouse_id": 1,
  "to_warehouse_id": 2,
  "quantity": 10
}

Response (201):
{
  "status": "success",
  "message": "Stock transfer completed successfully.",
  "data": {
    "id": 1,
    "inventory_item_id": 1,
    "from_warehouse_id": 1,
    "to_warehouse_id": 2,
    "quantity": 10,
    "transferred_at": "2026-02-08T10:00:00Z"
  }
}

Validation:
- Source warehouse must have sufficient quantity
- Source and destination warehouses must be different
- All items and warehouses must exist
```

#### Get Item Transfer History
```
GET /api/stock-transfers/item-history
Query Parameters:
  - item_id: int (required)
  - page: int (default: 1)
  - per_page: int (default: 15)

Response:
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "inventory_item_id": 1,
      "from_warehouse_id": 1,
      "to_warehouse_id": 2,
      "quantity": 10,
      "transferred_at": "2026-02-08T10:00:00Z"
    }
  ],
  "pagination": {...}
}
```

## Database Transactions

Stock transfers use database transactions to ensure data consistency:
- Both source and destination stock quantities are updated atomically
- If any operation fails, all changes are rolled back
- Transfer record is only created if both stock updates succeed

## Error Responses

### Validation Error (422)
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be a valid number."]
  }
}
```

### Resource Not Found (404)
```json
{
  "status": "error",
  "message": "Resource not found"
}
```

### Insufficient Stock (422)
```json
{
  "status": "error",
  "message": "Insufficient quantity in the source warehouse."
}
```

## Testing the API

### Using cURL

#### List inventory items with search:
```bash
curl -X GET "http://localhost:8000/api/inventory-items?search=laptop"
```

#### Create a warehouse:
```bash
curl -X POST "http://localhost:8000/api/warehouses" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Warehouse","location":"Test Location"}'
```

#### Transfer stock:
```bash
curl -X POST "http://localhost:8000/api/stock-transfers" \
  -H "Content-Type: application/json" \
  -d '{
    "inventory_item_id": 1,
    "from_warehouse_id": 1,
    "to_warehouse_id": 2,
    "quantity": 5
  }'
```

### Using Laravel Tinker or Tests
```php
// Search for items by name
$items = \App\Models\InventoryItem::searchByName('Laptop')->get();

// Search items in price range
$items = \App\Models\InventoryItem::priceRange(100, 500)->get();

// Get items with pagination
$paginated = \App\Models\InventoryItem::paginate(15);

// Get cached warehouses
$warehouses = \Illuminate\Support\Facades\Cache::remember('warehouses.all', 3600, function () {
    return \App\Models\Warehouse::with('stocks')->get();
});
```

## Event Listeners

The `LowStockDetected` event is automatically triggered when:
1. Stock is created with quantity < 10
2. Stock is updated and quantity drops below 10
3. Stock quantity is less than 10 (via model boot method)

The `SendLowStockAlert` listener:
- Logs the low stock event
- Includes warehouse name, item name, current quantity, and threshold
- Ready for email notification implementation

To implement email notifications, modify `SendLowStockAlert` to use:
```php
Mail::to(config('warehouse.admin_email'))->send(new LowStockNotification($event->stock, $event->threshold));
```

## Performance Optimizations

1. **Eager Loading**: Models use eager loading to prevent N+1 queries
2. **Caching**: Warehouse lists and inventory are cached for 1 hour
3. **Database Indexes**: Stock transfers table has indexed foreign keys
4. **Pagination**: All list endpoints support pagination to limit data transfer
5. **Query Optimization**: Search queries use optimized scopes
6. **Database Transactions**: Stock transfers use atomic transactions

## Setup Instructions

1. Clone the repository
2. Copy `.env.example` to `.env` and configure database connection
3. Run `php artisan key:generate`
4. Run `php artisan migrate`
5. Run `php artisan db:seed` to populate test data
6. Start the server: `php artisan serve`
7. Access the API at `http://localhost:8000/api/*`
