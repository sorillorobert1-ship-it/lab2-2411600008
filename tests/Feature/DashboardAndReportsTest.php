<?php

namespace Tests\Feature;

use App\Services\SupplierPerformanceService;

class DashboardAndReportsTest extends Lab7TestCase
{
    public function test_dashboard_widgets_and_api_endpoints(): void
    {
        $supplier = $this->supplier(['name' => 'Widget Supplier']);
        $order = $this->order($supplier, $this->product($supplier, ['quantity_on_hand' => 1]));
        $user = $this->user('manager');

        $this->actingAs($user)->get('/')->assertOk()->assertSee('Pending approvals')->assertSee('Reorder alerts')->assertSee($order->reference_number);

        $this->actingAs($user)->getJson('/api/dashboard/pending-orders')->assertOk()->assertJsonPath('count', 1);
        $this->actingAs($user)->getJson('/api/dashboard/recent-orders')->assertOk()->assertJsonPath('data.0.reference_number', $order->reference_number);
        $this->actingAs($user)->getJson('/api/dashboard/supplier-performance')->assertOk()->assertJsonStructure(['data']);
        $this->actingAs($user)->getJson('/api/dashboard/reorder-alerts')->assertOk()->assertJsonStructure(['data']);
    }

    public function test_supplier_performance_metrics(): void
    {
        $supplier = $this->supplier(['lead_time_days' => 5]);
        $product = $this->product($supplier);
        $onTime = $this->order($supplier, $product, 'received');
        $onTime->update(['order_date' => now()->subDays(10), 'expected_delivery_date' => now()->subDays(5), 'received_at' => now()->subDays(6)]);
        $onTime->items()->update(['received_quantity' => 10]);
        $late = $this->order($supplier, $product, 'received');
        $late->update(['order_date' => now()->subDays(10), 'expected_delivery_date' => now()->subDays(5), 'received_at' => now()->subDays(1)]);
        $late->items()->update(['received_quantity' => 8]);

        $metrics = app(SupplierPerformanceService::class)->calculatePerformance($supplier->fresh());

        $this->assertSame(2, $metrics['total_orders']);
        $this->assertEquals(50, $metrics['on_time_rate']);
        $this->assertEquals(50, $metrics['accuracy_rate']);
        $this->assertEquals(6.5, $metrics['average_delivery_days']);

        $this->actingAs($this->user('manager'))->get('/reports/supplier-performance')->assertOk()->assertSee('Supplier Performance Report');
        $this->actingAs($this->user('staff'))->get('/reports/supplier-performance')->assertForbidden();
    }

    public function test_products_and_transactions_search(): void
    {
        $supplier = $this->supplier();
        $this->product($supplier, ['name' => 'Bath Towel', 'category' => 'Linens']);
        $this->product($supplier, ['name' => 'Coffee Sachet', 'category' => 'Food']);
        $user = $this->user('manager');

        $this->actingAs($user)->get('/products?search=Towel')->assertSee('Bath Towel')->assertDontSee('Coffee Sachet');
        $this->actingAs($user)->get('/products?category=Food')->assertSee('Coffee Sachet')->assertDontSee('Bath Towel');
        $this->actingAs($user)->get('/inventory?type=in')->assertOk();
    }

    public function test_csv_and_pdf_exports(): void
    {
        $supplier = $this->supplier();
        $this->order($supplier, $this->product($supplier, ['name' => 'Export Me']));
        $user = $this->user('manager');

        $csv = $this->actingAs($user)->get('/exports/products/csv');
        $csv->assertOk();
        $this->assertStringContainsString('Export Me', $csv->streamedContent());

        foreach (['products', 'purchase-orders', 'transactions', 'supplier-performance'] as $export) {
            $this->actingAs($user)->get("/exports/{$export}/pdf")->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->actingAs($user)->get("/exports/{$export}/csv")->assertOk();
        }
    }
}
