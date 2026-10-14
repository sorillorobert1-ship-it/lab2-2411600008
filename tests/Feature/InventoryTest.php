<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_record_stock_in(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Test Towel',
            'sku' => 'TEST-TOWEL',
            'category' => 'Test',
            'quantity' => 5,
            'reorder_level' => 2,
            'unit_price' => 100,
        ]);

        $response = $this->actingAs($user)->post(route('inventory.store'), [
            'product_id' => $product->id,
            'transaction_type' => 'stock_in',
            'quantity' => 3,
            'reference_document' => 'TEST-IN-001',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 8]);
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'transaction_type' => 'stock_in',
            'quantity' => 3,
            'quantity_before' => 5,
            'quantity_after' => 8,
        ]);
    }

    public function test_stock_out_cannot_exceed_available_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Test Soap',
            'sku' => 'TEST-SOAP',
            'category' => 'Test',
            'quantity' => 2,
            'reorder_level' => 1,
            'unit_price' => 50,
        ]);

        $response = $this->actingAs($user)->post(route('inventory.store'), [
            'product_id' => $product->id,
            'transaction_type' => 'stock_out',
            'quantity' => 3,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 2]);
        $this->assertDatabaseMissing('inventory_transactions', ['product_id' => $product->id, 'transaction_type' => 'stock_out']);
    }

    public function test_inventory_api_returns_current_stock_summary(): void
    {
        $user = User::factory()->create();
        Product::create([
            'name' => 'Low Stock Item',
            'sku' => 'TEST-LOW',
            'category' => 'Test',
            'quantity' => 1,
            'reorder_level' => 5,
            'unit_price' => 25,
        ]);

        $response = $this->actingAs($user)->getJson(route('inventory.api'));

        $response->assertOk()
            ->assertJsonPath('total_products', 1)
            ->assertJsonPath('total_quantity', 1)
            ->assertJsonPath('low_stock', 1)
            ->assertJsonPath('inventory_value', 25);
    }

    public function test_physical_adjustment_updates_stock_and_is_audited(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Adjustment Item',
            'sku' => 'TEST-ADJUSTMENT',
            'category' => 'Test',
            'quantity' => 10,
            'reorder_level' => 3,
            'unit_price' => 20,
        ]);

        $response = $this->actingAs($user)->post(route('inventory.adjust'), [
            'product_id' => $product->id,
            'physical_quantity' => 7,
            'reason' => 'Damaged goods',
            'notes' => 'Three items damaged during inspection.',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'quantity' => 7]);
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'transaction_type' => 'adjustment',
            'quantity' => 3,
            'quantity_before' => 10,
            'quantity_after' => 7,
        ]);
    }
}
