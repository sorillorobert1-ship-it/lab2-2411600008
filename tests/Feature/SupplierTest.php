<?php

namespace Tests\Feature;

use App\Models\Supplier;

class SupplierTest extends Lab7TestCase
{
    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/suppliers')->assertRedirect('/login');
    }

    public function test_manager_can_create_update_and_view_supplier(): void
    {
        $manager = $this->user('manager');

        $this->actingAs($manager)->post('/suppliers', [
            'code' => 'SUP-100', 'name' => 'Acme Linens', 'contact_person' => 'Ana', 'email' => 'ana@acme.test',
            'phone' => '0917-123-4567', 'payment_terms' => 'Net 30', 'lead_time_days' => 7, 'minimum_order_amount' => 5000, 'is_active' => 1,
        ])->assertRedirect();

        $supplier = Supplier::where('code', 'SUP-100')->firstOrFail();
        $this->actingAs($manager)->put("/suppliers/{$supplier->id}", $supplier->only(['code', 'contact_person', 'email', 'phone', 'payment_terms', 'lead_time_days', 'minimum_order_amount']) + ['name' => 'Acme Linens Corp'])->assertRedirect();

        $this->assertSame('Acme Linens Corp', $supplier->fresh()->name);
        $this->actingAs($manager)->get("/suppliers/{$supplier->id}")->assertOk()->assertSee('Acme Linens Corp');
    }

    public function test_supplier_validation_rejects_duplicate_email_and_bad_phone(): void
    {
        $existing = $this->supplier();

        $this->actingAs($this->user('manager'))->post('/suppliers', [
            'code' => 'SUP-200', 'name' => 'Dup', 'contact_person' => 'X', 'email' => $existing->email,
            'phone' => '12345', 'payment_terms' => 'Net 30', 'lead_time_days' => 7, 'minimum_order_amount' => 0,
        ])->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_index_search_and_status_filter(): void
    {
        $this->supplier(['name' => 'Linen Masters']);
        $this->supplier(['name' => 'Water World', 'is_active' => false]);

        $this->actingAs($this->user('staff'))->get('/suppliers?search=Linen')->assertSee('Linen Masters')->assertDontSee('Water World');
        $this->actingAs($this->user('staff'))->get('/suppliers?status=inactive')->assertSee('Water World')->assertDontSee('Linen Masters');
    }

    public function test_staff_cannot_manage_suppliers(): void
    {
        $supplier = $this->supplier();
        $staff = $this->user('staff');

        $this->actingAs($staff)->get('/suppliers/create')->assertForbidden();
        $this->actingAs($staff)->delete("/suppliers/{$supplier->id}")->assertForbidden();
    }

    public function test_soft_deleted_supplier_is_hidden_but_still_referenced_by_orders(): void
    {
        $supplier = $this->supplier(['name' => 'Closing Down Inc']);
        $order = $this->order($supplier, $this->product($supplier), 'received');
        $manager = $this->user('manager');

        $this->actingAs($manager)->delete("/suppliers/{$supplier->id}")->assertRedirect('/suppliers');

        $this->assertSoftDeleted($supplier);
        $this->actingAs($manager)->get('/suppliers')->assertDontSee('<td class="fw-semibold">Closing Down Inc</td>', false);
        $this->assertSame('Closing Down Inc', $order->fresh()->supplier->name);
        $this->actingAs($manager)->get("/purchase-orders/{$order->id}")->assertSee('Closing Down Inc');
    }

    public function test_supplier_with_open_orders_cannot_be_deleted(): void
    {
        $supplier = $this->supplier();
        $this->order($supplier, $this->product($supplier), 'pending');

        $this->actingAs($this->user('manager'))->delete("/suppliers/{$supplier->id}")->assertSessionHas('error');
        $this->assertNotSoftDeleted($supplier);
    }

    public function test_scopes_and_accessors(): void
    {
        $this->supplier(['lead_time_days' => 3]);
        $this->supplier(['lead_time_days' => 15]);
        $this->supplier(['is_active' => false]);

        $this->assertSame(2, Supplier::active()->count());
        $this->assertSame(2, Supplier::withLeadTime(5)->count());
        $this->assertStringContainsString('Tester', Supplier::first()->full_contact);
    }
}
