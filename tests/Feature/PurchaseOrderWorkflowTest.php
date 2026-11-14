<?php

namespace Tests\Feature;

use App\Mail\PurchaseOrderApproved;
use App\Mail\PurchaseOrderCreated;
use App\Mail\PurchaseOrderSent;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Mail;

class PurchaseOrderWorkflowTest extends Lab7TestCase
{
    public function test_staff_creates_order_with_multiple_line_items(): void
    {
        Mail::fake();
        $this->user('manager');
        $supplier = $this->supplier();
        [$a, $b] = [$this->product($supplier), $this->product($supplier)];

        $this->actingAs($this->user('staff'))->post('/purchase-orders', [
            'supplier_id' => $supplier->id, 'order_date' => now()->toDateString(), 'action' => 'submit',
            'items' => [['product_id' => $a->id, 'quantity' => 10, 'unit_price' => 100], ['product_id' => $b->id, 'quantity' => 5, 'unit_price' => 20]],
        ])->assertRedirect();

        $order = PurchaseOrder::with('items')->firstOrFail();
        $this->assertSame('pending', $order->status);
        $this->assertCount(2, $order->items);
        $this->assertEquals(1100, $order->subtotal);
        $this->assertEquals(1232, $order->total);
        $this->assertMatchesRegularExpression('/^PO-\d{8}-[A-Z0-9]{5}$/', $order->reference_number);
        Mail::assertQueued(PurchaseOrderCreated::class);
    }

    public function test_order_requires_active_supplier_and_line_items(): void
    {
        $inactive = $this->supplier(['is_active' => false]);

        $this->actingAs($this->user('staff'))->post('/purchase-orders', [
            'supplier_id' => $inactive->id, 'order_date' => now()->toDateString(), 'items' => [],
        ])->assertSessionHasErrors(['supplier_id', 'items']);
    }

    public function test_warehouse_staff_cannot_create_orders(): void
    {
        $this->actingAs($this->user('warehouse'))->get('/purchase-orders/create')->assertForbidden();
    }

    public function test_only_managers_can_approve_and_creator_is_notified(): void
    {
        Mail::fake();
        $staff = $this->user('staff');
        $supplier = $this->supplier();
        $order = $this->order($supplier, $this->product($supplier), 'pending', $staff);

        $this->actingAs($staff)->post("/purchase-orders/{$order->id}/approve")->assertForbidden();
        $this->actingAs($this->user('warehouse'))->post("/purchase-orders/{$order->id}/approve")->assertForbidden();

        $manager = $this->user('manager');
        $this->actingAs($manager)->post("/purchase-orders/{$order->id}/approve")->assertRedirect();

        $order->refresh();
        $this->assertSame('approved', $order->status);
        $this->assertSame($manager->id, $order->approved_by);
        $this->assertNotNull($order->approved_at);
        $this->assertSame(1, $staff->notifications()->count());
        Mail::assertQueued(PurchaseOrderApproved::class, fn ($mail) => $mail->hasTo($staff->email));
        $this->assertDatabaseHas('purchase_order_histories', ['purchase_order_id' => $order->id, 'from_status' => 'pending', 'to_status' => 'approved', 'user_id' => $manager->id]);
    }

    public function test_manager_can_reject_with_reason(): void
    {
        $supplier = $this->supplier();
        $order = $this->order($supplier, $this->product($supplier));
        $manager = $this->user('manager');

        $this->actingAs($manager)->post("/purchase-orders/{$order->id}/reject", [])->assertSessionHasErrors('rejection_reason');
        $this->actingAs($manager)->post("/purchase-orders/{$order->id}/reject", ['rejection_reason' => 'Too expensive'])->assertRedirect();

        $this->assertSame('rejected', $order->fresh()->status);
        $this->assertSame('Too expensive', $order->fresh()->rejection_reason);
    }

    public function test_send_emails_the_supplier(): void
    {
        Mail::fake();
        $supplier = $this->supplier();
        $order = $this->order($supplier, $this->product($supplier), 'approved');

        $this->actingAs($this->user('manager'))->post("/purchase-orders/{$order->id}/send")->assertRedirect();

        $this->assertSame('sent', $order->fresh()->status);
        Mail::assertQueued(PurchaseOrderSent::class, fn ($mail) => $mail->hasTo($supplier->email));
    }

    public function test_partial_and_full_receiving_updates_stock_and_creates_transactions(): void
    {
        $supplier = $this->supplier();
        $product = $this->product($supplier, ['quantity_on_hand' => 5]);
        $order = $this->order($supplier, $product, 'sent');
        $item = $order->items->first();
        $warehouse = $this->user('warehouse');

        $this->actingAs($this->user('staff'))->post("/purchase-orders/{$order->id}/receive", ['received' => [$item->id => 4]])->assertForbidden();

        $this->actingAs($warehouse)->post("/purchase-orders/{$order->id}/receive", ['received' => [$item->id => 4]])->assertRedirect();
        $this->assertSame('partially_received', $order->fresh()->status);
        $this->assertSame(9, $product->fresh()->quantity_on_hand);

        $this->actingAs($warehouse)->post("/purchase-orders/{$order->id}/receive", ['received' => [$item->id => 6]])->assertRedirect();
        $this->assertSame('received', $order->fresh()->status);
        $this->assertSame(15, $product->fresh()->quantity_on_hand);
        $this->assertSame(2, $order->inventoryTransactions()->count());
        $this->assertDatabaseHas('inventory_transactions', ['product_id' => $product->id, 'type' => 'in', 'quantity' => 6, 'reference_type' => PurchaseOrder::class, 'reference_id' => $order->id]);
    }

    public function test_only_draft_orders_can_be_edited_by_creator_and_deleted_by_admin(): void
    {
        $staff = $this->user('staff');
        $supplier = $this->supplier();
        $draft = $this->order($supplier, $this->product($supplier), 'draft', $staff);
        $pending = $this->order($supplier, $this->product($supplier), 'pending', $staff);

        $this->actingAs($staff)->get("/purchase-orders/{$draft->id}/edit")->assertOk();
        $this->actingAs($staff)->get("/purchase-orders/{$pending->id}/edit")->assertForbidden();
        $this->actingAs($this->user('staff'))->get("/purchase-orders/{$draft->id}/edit")->assertForbidden();

        $this->actingAs($staff)->delete("/purchase-orders/{$draft->id}")->assertForbidden();
        $this->actingAs($this->user('admin'))->delete("/purchase-orders/{$draft->id}")->assertRedirect();
        $this->assertSoftDeleted($draft);
    }

    public function test_index_filters_by_status_and_supplier(): void
    {
        $a = $this->supplier(['name' => 'Alpha']);
        $b = $this->supplier(['name' => 'Bravo']);
        $pa = $this->order($a, $this->product($a), 'pending');
        $pb = $this->order($b, $this->product($b), 'received');
        $user = $this->user('staff');

        $this->actingAs($user)->get('/purchase-orders?status=pending')->assertSee($pa->reference_number)->assertDontSee($pb->reference_number);
        $this->actingAs($user)->get("/purchase-orders?supplier_id={$b->id}")->assertSee($pb->reference_number)->assertDontSee($pa->reference_number);
    }

    public function test_order_pages_render(): void
    {
        $supplier = $this->supplier();
        $order = $this->order($supplier, $this->product($supplier));
        $manager = $this->user('manager');

        $this->actingAs($manager)->get('/purchase-orders')->assertOk();
        $this->actingAs($manager)->get('/purchase-orders/create')->assertOk()->assertSee('Add item');
        $this->actingAs($manager)->get("/purchase-orders/{$order->id}")->assertOk()->assertSee('Approval history')->assertSee('Approve');
        $this->actingAs($manager)->get("/purchase-orders/{$order->id}/pdf")->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
