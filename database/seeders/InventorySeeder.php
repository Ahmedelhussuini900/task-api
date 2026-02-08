<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create warehouses
        $warehouse1 = Warehouse::create([
            'name' => 'Main Warehouse',
            'location' => 'New York',
        ]);

        $warehouse2 = Warehouse::create([
            'name' => 'Secondary Warehouse',
            'location' => 'Los Angeles',
        ]);

        $warehouse3 = Warehouse::create([
            'name' => 'Regional Hub',
            'location' => 'Chicago',
        ]);

        // Create inventory items
        $item1 = InventoryItem::create([
            'name' => 'Laptop',
            'sku' => 'LAPTOP-001',
            'description' => 'High-performance laptop',
            'price' => 1299.99,
        ]);

        $item2 = InventoryItem::create([
            'name' => 'Mouse',
            'sku' => 'MOUSE-001',
            'description' => 'Wireless mouse',
            'price' => 29.99,
        ]);

        $item3 = InventoryItem::create([
            'name' => 'Keyboard',
            'sku' => 'KEYBOARD-001',
            'description' => 'Mechanical keyboard',
            'price' => 149.99,
        ]);

        $item4 = InventoryItem::create([
            'name' => 'Monitor',
            'sku' => 'MONITOR-001',
            'description' => '4K Display Monitor',
            'price' => 499.99,
        ]);

        $item5 = InventoryItem::create([
            'name' => 'USB Cable',
            'sku' => 'CABLE-001',
            'description' => 'USB-C to USB-A cable',
            'price' => 9.99,
        ]);

        // Create stocks with varying quantities (some low to trigger events)
        Stock::create([
            'warehouse_id' => $warehouse1->id,
            'inventory_item_id' => $item1->id,
            'quantity' => 25,
        ]);

        Stock::create([
            'warehouse_id' => $warehouse1->id,
            'inventory_item_id' => $item2->id,
            'quantity' => 5, // Low stock
        ]);

        Stock::create([
            'warehouse_id' => $warehouse1->id,
            'inventory_item_id' => $item3->id,
            'quantity' => 100,
        ]);

        Stock::create([
            'warehouse_id' => $warehouse2->id,
            'inventory_item_id' => $item1->id,
            'quantity' => 15,
        ]);

        Stock::create([
            'warehouse_id' => $warehouse2->id,
            'inventory_item_id' => $item4->id,
            'quantity' => 8, // Low stock
        ]);

        Stock::create([
            'warehouse_id' => $warehouse2->id,
            'inventory_item_id' => $item5->id,
            'quantity' => 250,
        ]);

        Stock::create([
            'warehouse_id' => $warehouse3->id,
            'inventory_item_id' => $item2->id,
            'quantity' => 50,
        ]);

        Stock::create([
            'warehouse_id' => $warehouse3->id,
            'inventory_item_id' => $item3->id,
            'quantity' => 3, // Low stock
        ]);

        Stock::create([
            'warehouse_id' => $warehouse3->id,
            'inventory_item_id' => $item4->id,
            'quantity' => 12,
        ]);
    }
}
