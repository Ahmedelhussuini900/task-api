# Developer Quick Reference Guide

## Adding a New Entity to the System

This guide shows how to add a new entity (e.g., Category) to the warehouse management system following the Repository and Service patterns.

### Step 1: Create the Model and Migration

```php
// Create model and migration
php artisan make:model Category -m

// In migration file (database/migrations/xxxx_create_categories_table.php)
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->text('description')->nullable();
    $table->timestamps();
});

// In Model (app/Models/Category.php)
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'description'];

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('name', 'like', '%' . $term . '%')
                     ->orWhere('description', 'like', '%' . $term . '%');
    }
}
```

### Step 2: Create Repository Interface

```php
// app/Repositories/Contracts/CategoryRepositoryInterface.php
<?php

namespace App\Repositories\Contracts;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function search(string $term, int $perPage = 15);
    public function findByName(string $name);
}
```

### Step 3: Create Repository Implementation

```php
// app/Repositories/Eloquent/CategoryRepository.php
<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    protected function getModel(): string
    {
        return Category::class;
    }

    public function search(string $term, int $perPage = 15)
    {
        return $this->model
            ->search($term)
            ->paginate($perPage);
    }

    public function findByName(string $name)
    {
        return $this->model->where('name', $name)->first();
    }
}
```

### Step 4: Create Service

```php
// app/Services/CategoryService.php
<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getAllCategories(int $perPage = 15)
    {
        return $this->categoryRepository->paginate($perPage);
    }

    public function getCategory(int $id)
    {
        return $this->categoryRepository->find($id);
    }

    public function search(string $term, int $perPage = 15)
    {
        return $this->categoryRepository->search($term, $perPage);
    }

    public function createCategory(array $data)
    {
        return $this->categoryRepository->create($data);
    }

    public function updateCategory(int $id, array $data)
    {
        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }
}
```

### Step 5: Create Form Request

```php
// app/Http/Requests/StoreCategoryRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
```

### Step 6: Create Controller

```php
// app/Http/Controllers/CategoryController.php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);

        if ($request->has('search')) {
            $categories = $this->categoryService->search(
                $request->get('search'),
                $perPage
            );
        } else {
            $categories = $this->categoryService->getAllCategories($perPage);
        }

        return response()->json([
            'status' => 'success',
            'data' => $categories->items(),
            'pagination' => [
                'total' => $categories->total(),
                'per_page' => $categories->perPage(),
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        $data = $this->categoryService->getCategory($category->id);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $updated = $this->categoryService->updateCategory(
            $category->id,
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully.',
            'data' => $updated,
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->deleteCategory($category->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully.',
        ]);
    }
}
```

### Step 7: Register in Service Provider

```php
// Update app/Providers/RepositoryServiceProvider.php

public function register(): void
{
    // ... existing bindings ...

    $this->app->bind(
        CategoryRepositoryInterface::class,
        CategoryRepository::class
    );

    $this->app->singleton(CategoryService::class, function ($app) {
        return new CategoryService(
            $app->make(CategoryRepositoryInterface::class)
        );
    });
}
```

### Step 8: Add Routes

```php
// routes/api.php
Route::apiResource('categories', CategoryController::class);
```

### Step 9: Run Migration

```bash
php artisan migrate
```

## Common Service Layer Patterns

### Pattern 1: Simple CRUD Service

```php
class EntityService
{
    public function __construct(
        private EntityRepositoryInterface $repository
    ) {}

    public function getAll(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function get(int $id)
    {
        return $this->repository->find($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
```

### Pattern 2: Service with Business Logic

```php
class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ItemRepositoryInterface $itemRepository,
        private StockRepositoryInterface $stockRepository
    ) {}

    public function placeOrder(array $orderData): Order
    {
        try {
            return DB::transaction(function () use ($orderData) {
                $order = $this->orderRepository->create($orderData);

                foreach ($orderData['items'] as $item) {
                    // Update stock
                    $stock = $this->stockRepository->find($item['stock_id']);
                    $this->stockRepository->adjustQuantity(
                        $stock->id,
                        -$item['quantity']
                    );
                }

                return $order;
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to place order: ' . $e->getMessage());
        }
    }
}
```

### Pattern 3: Service with Caching

```php
class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
        private CacheManager $cache
    ) {}

    public function getAll(int $perPage = 15)
    {
        return $this->cache->remember(
            'categories_page_' . request()->get('page', 1),
            3600,
            fn() => $this->repository->paginate($perPage)
        );
    }

    public function create(array $data)
    {
        $category = $this->repository->create($data);
        $this->cache->forget('categories_*');
        return $category;
    }
}
```

## Repository Query Patterns

### Simple Query
```php
public function getActive()
{
    return $this->model->where('active', true)->get();
}
```

### With Relationships
```php
public function getWithRelations(int $id)
{
    return $this->model->with(['orders', 'reviews'])->find($id);
}
```

### Complex Query
```php
public function findExpensiveInActive(float $price)
{
    return $this->model
        ->where('active', true)
        ->where('price', '>', $price)
        ->with('categories')
        ->orderBy('price', 'desc')
        ->paginate(15);
}
```

### Using Scopes
```php
public function search(string $term)
{
    return $this->model
        ->active()
        ->search($term)
        ->withRelations()
        ->paginate(15);
}
```

## Testing Examples

### Service Test
```php
class CategoryServiceTest extends TestCase
{
    private CategoryService $service;
    private CategoryRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(CategoryRepositoryInterface::class);
        $this->service = new CategoryService($this->repository);
    }

    public function test_can_create_category()
    {
        $data = ['name' => 'Test Category'];
        $this->repository->shouldReceive('create')
            ->with($data)
            ->andReturn(new Category($data));

        $result = $this->service->createCategory($data);

        $this->assertEquals('Test Category', $result->name);
    }
}
```

### Controller Test
```php
class CategoryControllerTest extends TestCase
{
    public function test_can_list_categories()
    {
        $response = $this->get('/api/categories');

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'pagination' => [
                        'total' => 0,
                    ]
                ]);
    }
}
```

## Best Practices

1. **Always use dependency injection** in services and controllers
2. **Keep services focused** - one service per entity
3. **Use repositories for all database access** - never use Model directly in services
4. **Handle exceptions** in services and throw meaningful exceptions
5. **Use database transactions** for operations that affect multiple entities
6. **Cache strategically** - focus on frequently read, infrequently updated data
7. **Validate input** in Form Requests, not in services
8. **Use events** for loose coupling between entities
9. **Document complex** business logic in service methods
10. **Write tests** for services and repositories

## Troubleshooting

### Issue: Service not injected
**Solution:** Ensure the service is registered in `RepositoryServiceProvider`

### Issue: Repository queries not working
**Solution:** Check that the model's accessor method is correct and exists

### Issue: Cache not clearing
**Solution:** Verify `Cache::forget()` is called with correct keys after create/update/delete

### Issue: Pagination not working
**Solution:** Ensure you're calling `paginate()` not `get()` in repositories

### Issue: Relationships not loading
**Solution:** Use `with()` in repository to eager load relationships

## Performance Tips

1. **Use eager loading** with `with()` to prevent N+1 queries
2. **Add database indexes** on frequently queried columns
3. **Use paginate()** instead of `get()` for large datasets
4. **Cache expensive** queries with `remember()`
5. **Use select()** to fetch only needed columns
6. **Add query logging** during development to spot issues

```php
// Enable query logging
if (app()->isLocal()) {
    \DB::listen(function ($query) {
        \Log::info($query->sql, $query->bindings);
    });
}
```

## File Checklist for New Entity

- [ ] Model with fillable and scopes
- [ ] Migration file
- [ ] Repository Interface
- [ ] Repository Implementation
- [ ] Service class
- [ ] Form Requests (Store and Update)
- [ ] Controller with all CRUD methods
- [ ] Routes (apiResource)
- [ ] Service registered in RepositoryServiceProvider
- [ ] Tests for Service
- [ ] Tests for Controller
- [ ] API documentation

Good luck building amazing features! 🚀
