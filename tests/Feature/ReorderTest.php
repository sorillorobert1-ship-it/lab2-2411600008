<?php

namespace Tests\Feature;

use App\Mail\LowStockAlert;
use App\Models\PurchaseOrder;
use App\Services\ReorderService;
use Illuminate\Support\Facades\Mail;

class ReorderTest extends Lab7TestCase
{
    private function withUsage($product, int $perDay, int $days = 30): void
    {
        foreach (range(1, $days) as $d) {
            $product->inventoryTransactions()->make(['type' => 'out', 'quantity' => $perDay])
                ->forceFill(['created_at' => now()->subDays($d)])->save();
        }
    }

    public function test_reorder_point_formula(): void
    {
        $supplier = $this->supplier(['lead_time_days' => 7]);
        $product = $this->product($supplier, ['safety_stock' => 20]);
        $this->withUsage($product, 10);

        $service = app(ReorderService::class);
        $this->assertEquals(10, $service->calculateAverageDailyUsage($product));
        $this->assertSame(90, $service->calculateReorderPoint($product)); // (10 x 7) + 20
    }

    public function test_products_needing_reorder_are_grouped_into_draft_orders_per_supplier(): void
    {
        $a = $this->supplier();
        $b = $this->supplier();
        $low1 = $this->product($a, ['quantity_on_hand' => 5]);
        $low2 = $this->product($a, ['quantity_on_hand' => 8]);
        $low3 = $this->product($b, ['quantity_on_hand' => 1]);
        $this->product($b, ['quantity_on_hand' => 500]);

        $orders = app(ReorderService::class)->generatePurchaseOrders();

        $this->assertCount(2, $orders);
        $this->assertTrue($orders->every(fn ($o) => $o->status === 'draft' && $o->is_auto_generated));
        $this->assertSame(2, $orders->firstWhere('supplier_id', $a->id)->items->count());

        // A second run must not duplicate orders for products already on an open PO.
        $this->assertCount(0, app(ReorderService::class)->generatePurchaseOrders());
    }

    public function test_command_generates_orders_and_alerts_admins(): void
    {
        Mail::fake();
        $this->user('admin');
        $supplier = $this->supplier();
        $this->product($supplier, ['quantity_on_hand' => 2]);

        $this->artisan('inventory:check-reorder')
            ->expectsOutputToContain('Generated 1 purchase order(s)')
            ->assertSuccessful();

        $this->assertSame(1, PurchaseOrder::count());
        Mail::assertQueued(LowStockAlert::class);
    }

    public function test_command_is_scheduled_daily(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('inventory:check-reorder')->assertSuccessful();
    }
}
