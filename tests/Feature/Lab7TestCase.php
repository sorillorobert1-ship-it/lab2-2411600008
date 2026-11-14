<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class Lab7TestCase extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    protected function supplier(array $attributes = []): Supplier
    {
        static $n = 0;
        $n++;

        return Supplier::create($attributes + [
            'code' => "SUP-T{$n}", 'name' => "Test Supplier {$n}", 'contact_person' => 'Tester',
            'email' => "supplier{$n}@test.com", 'phone' => '0917-555-0101', 'payment_terms' => 'Net 30',
            'lead_time_days' => 5, 'minimum_order_amount' => 1000, 'is_active' => true,
        ]);
    }

    protected function product(Supplier $supplier, array $attributes = []): Product
    {
        static $n = 0;
        $n++;

        return Product::create($attributes + [
            'sku' => "SKU-{$n}", 'name' => "Product {$n}", 'unit' => 'piece', 'quantity_on_hand' => 100,
            'reorder_level' => 20, 'safety_stock' => 10, 'unit_cost' => 50, 'supplier_id' => $supplier->id, 'is_active' => true,
        ]);
    }

    protected function order(Supplier $supplier, Product $product, string $status = 'pending', ?User $creator = null): PurchaseOrder
    {
        $order = PurchaseOrder::create([
            'reference_number' => PurchaseOrder::generateReferenceNumber(), 'supplier_id' => $supplier->id,
            'created_by' => $creator?->id, 'status' => $status, 'order_date' => now(), 'expected_delivery_date' => now()->addDays(5),
        ]);
        $order->items()->create(['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 50]);

        return $order->calculateTotal();
    }
}
