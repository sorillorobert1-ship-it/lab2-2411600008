<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class Lab7Seeder extends Seeder
{
    public function run(): void
    {
        $users = collect([
            ['email' => 'admin@roberthotel.com', 'name' => 'Robert Admin', 'role' => 'admin'],
            ['email' => 'manager@example.com', 'name' => 'Lab Manager', 'role' => 'manager'],
            ['email' => 'warehouse@roberthotel.com', 'name' => 'Wally Warehouse', 'role' => 'warehouse'],
            ['email' => 'staff@roberthotel.com', 'name' => 'Paula Purchasing', 'role' => 'staff'],
        ])->mapWithKeys(fn ($u) => [$u['role'] => User::updateOrCreate(['email' => $u['email']], $u + ['password' => 'password'])]);

        $suppliers = collect([
            ['code' => 'SUP-001', 'name' => 'Mindanao Hotel Supplies Co.', 'contact_person' => 'Ana Santos', 'email' => 'orders@mindanaohotelsupplies.test', 'phone' => '0917-555-0101', 'address' => 'National Highway, General Santos City', 'payment_terms' => 'Net 30', 'lead_time_days' => 5, 'minimum_order_amount' => 5000, 'reliability_score' => 96],
            ['code' => 'SUP-002', 'name' => 'Linen Masters Inc.', 'contact_person' => 'Ben Cruz', 'email' => 'sales@linenmasters.test', 'phone' => '0918-222-3344', 'address' => 'J.P. Laurel Ave, Davao City', 'payment_terms' => 'Net 45', 'lead_time_days' => 10, 'minimum_order_amount' => 10000, 'reliability_score' => 88],
            ['code' => 'SUP-003', 'name' => 'Fresh Amenities Trading', 'contact_person' => 'Carla Reyes', 'email' => 'carla@freshamenities.test', 'phone' => '0920-111-2233', 'address' => 'Pioneer Ave, General Santos City', 'payment_terms' => 'Net 15', 'lead_time_days' => 3, 'minimum_order_amount' => 2000, 'reliability_score' => 92],
            ['code' => 'SUP-004', 'name' => 'SoCot Beverage Distributors', 'contact_person' => 'Dante Lim', 'email' => 'dante@socotbev.test', 'phone' => '0919-876-5432', 'address' => 'Koronadal City, South Cotabato', 'payment_terms' => 'COD', 'lead_time_days' => 2, 'minimum_order_amount' => 1500, 'reliability_score' => 80],
            ['code' => 'SUP-005', 'name' => 'BrightHome Maintenance Depot', 'contact_person' => 'Ella Tan', 'email' => 'ella@brighthome.test', 'phone' => '0916-333-4455', 'address' => 'Santiago Blvd, General Santos City', 'payment_terms' => 'Net 30', 'lead_time_days' => 7, 'minimum_order_amount' => 3000, 'reliability_score' => 75],
        ])->mapWithKeys(fn ($s) => [$s['code'] => Supplier::withTrashed()->updateOrCreate(['code' => $s['code']], $s + ['is_active' => true, 'deleted_at' => null])]);

        // [sku, name, category, unit, on hand, reorder level, safety stock, unit cost, primary supplier, daily usage, alt supplier]
        $catalogue = [
            ['TWL-BATH', 'Bath Towel (White, 70x140cm)', 'Linens', 'piece', 40, 60, 30, 320, 'SUP-002', 4, null],
            ['LIN-QSHEET', 'Queen Bed Sheet Set', 'Linens', 'set', 85, 40, 20, 950, 'SUP-002', 2, null],
            ['AMN-SHAMP', 'Shampoo Sachet 30ml', 'Guest Amenities', 'sachet', 120, 300, 150, 6.50, 'SUP-003', 25, 'SUP-001'],
            ['AMN-SOAP', 'Guest Soap Bar 40g', 'Guest Amenities', 'bar', 600, 250, 120, 9.75, 'SUP-003', 20, 'SUP-001'],
            ['AMN-SLIP', 'Disposable Slippers', 'Guest Amenities', 'pair', 90, 100, 50, 18, 'SUP-001', 8, null],
            ['HK-TISSUE', 'Toilet Tissue 2-ply', 'Housekeeping', 'roll', 150, 200, 100, 14, 'SUP-001', 18, null],
            ['HK-DETERG', 'Laundry Detergent 5kg', 'Housekeeping', 'pack', 25, 10, 5, 780, 'SUP-001', 0.5, null],
            ['FB-WATER', 'Bottled Water 500ml', 'Food & Beverage', 'bottle', 480, 300, 150, 11, 'SUP-004', 30, null],
            ['FB-COFFEE', '3-in-1 Coffee Sachet', 'Food & Beverage', 'sachet', 140, 150, 80, 8.50, 'SUP-004', 12, 'SUP-003'],
            ['MNT-BULB', 'LED Bulb 9W', 'Maintenance', 'piece', 60, 20, 10, 95, 'SUP-005', 1, null],
        ];

        InventoryTransaction::query()->delete();

        $products = collect($catalogue)->mapWithKeys(function ($row) use ($suppliers, $users) {
            [$sku, $name, $category, $unit, $onHand, $reorder, $safety, $cost, $primary, $dailyUsage, $alt] = $row;

            $product = Product::updateOrCreate(['sku' => $sku], [
                'name' => $name, 'description' => "{$name} for Robert Hotel rooms", 'category' => $category, 'unit' => $unit,
                'quantity_on_hand' => $onHand, 'quantity' => $onHand, 'reorder_level' => $reorder, 'safety_stock' => $safety,
                'unit_cost' => $cost, 'unit_price' => round($cost * 1.3, 2), 'supplier_id' => $suppliers[$primary]->id, 'is_active' => true,
            ]);

            $pivot = [$suppliers[$primary]->id => ['supplier_sku' => $primary.'-'.$sku, 'unit_cost' => round($cost * 0.95, 2), 'minimum_order_quantity' => max((int) ceil($dailyUsage * 5), 5), 'is_preferred' => true]];
            if ($alt) {
                $pivot[$suppliers[$alt]->id] = ['supplier_sku' => $alt.'-'.$sku, 'unit_cost' => $cost, 'minimum_order_quantity' => 10, 'is_preferred' => false];
            }
            $product->suppliers()->sync($pivot);

            // 30 days of housekeeping usage history for the average-daily-usage calculation.
            $level = $onHand + (int) round($dailyUsage * 30);
            foreach (range(30, 1) as $daysAgo) {
                $used = max((int) round($dailyUsage * (0.7 + (($daysAgo * 7) % 6) / 10)), $dailyUsage >= 1 ? 1 : ($daysAgo % 2));
                if ($used < 1) {
                    continue;
                }
                $date = Carbon::now()->subDays($daysAgo)->setTime(9, 0);
                $product->inventoryTransactions()->make([
                    'user_id' => $users['warehouse']->id, 'type' => 'out', 'transaction_type' => 'stock_out', 'quantity' => $used,
                    'quantity_before' => $level, 'quantity_after' => max($level - $used, 0),
                    'reference_document' => 'HK-ISSUE-'.$date->format('md'), 'notes' => 'Daily housekeeping issue',
                ])->forceFill(['created_at' => $date, 'updated_at' => $date])->save();
                $level = max($level - $used, 0);
            }

            return [$sku => $product];
        });

        PurchaseOrder::withTrashed()->get()->each->forceDelete();

        // [supplier, status, days ago ordered, actual delivery days (null = not delivered), [sku => [qty, received]]]
        $orders = [
            ['SUP-002', 'received', 75, 9, ['LIN-QSHEET' => [40, 40], 'TWL-BATH' => [120, 120]]],
            ['SUP-003', 'received', 60, 3, ['AMN-SOAP' => [800, 800], 'AMN-SHAMP' => [600, 600]]],
            ['SUP-001', 'received', 52, 4, ['HK-TISSUE' => [400, 400], 'AMN-SLIP' => [200, 200]]],
            ['SUP-004', 'received', 45, 4, ['FB-WATER' => [600, 600], 'FB-COFFEE' => [300, 280]]],
            ['SUP-005', 'received', 40, 11, ['MNT-BULB' => [50, 45]]],
            ['SUP-002', 'received', 30, 13, ['TWL-BATH' => [100, 100]]],
            ['SUP-003', 'received', 21, 2, ['AMN-SHAMP' => [500, 500]]],
            ['SUP-001', 'partially_received', 12, 6, ['HK-DETERG' => [20, 12], 'AMN-SLIP' => [150, 150]]],
            ['SUP-004', 'sent', 1, null, ['FB-WATER' => [400, 0]]],
            ['SUP-005', 'approved', 2, null, ['MNT-BULB' => [30, 0]]],
            ['SUP-001', 'pending', 1, null, ['HK-TISSUE' => [300, 0], 'AMN-SLIP' => [100, 0]]],
            ['SUP-002', 'draft', 0, null, ['LIN-QSHEET' => [20, 0]]],
            ['SUP-003', 'rejected', 5, null, ['AMN-SOAP' => [1000, 0]]],
        ];

        foreach ($orders as $index => [$code, $status, $daysAgo, $deliveryDays, $items]) {
            $supplier = $suppliers[$code];
            $orderDate = Carbon::now()->subDays($daysAgo)->startOfDay();
            $approved = ! in_array($status, ['draft', 'pending', 'rejected'], true);

            $order = PurchaseOrder::create([
                'reference_number' => 'PO-'.$orderDate->format('Ymd').'-'.str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'supplier_id' => $supplier->id,
                'created_by' => $users['staff']->id,
                'approved_by' => $approved || $status === 'rejected' ? $users['manager']->id : null,
                'status' => $status,
                'order_date' => $orderDate,
                'expected_delivery_date' => $orderDate->copy()->addDays($supplier->lead_time_days),
                'approved_at' => $approved ? $orderDate->copy()->addHours(3) : null,
                'sent_at' => $approved && $status !== 'approved' ? $orderDate->copy()->addHours(5) : null,
                'received_at' => $deliveryDays !== null ? $orderDate->copy()->addDays($deliveryDays)->setTime(14, 0) : null,
                'rejection_reason' => $status === 'rejected' ? 'Quantity too high for current occupancy forecast.' : null,
                'terms' => $supplier->payment_terms,
            ]);

            foreach ($items as $sku => [$quantity, $received]) {
                $order->items()->create(['product_id' => $products[$sku]->id, 'quantity' => $quantity, 'unit_price' => $products[$sku]->suppliers->firstWhere('id', $supplier->id)?->pivot->unit_cost ?? $products[$sku]->unit_cost, 'received_quantity' => $received]);

                if ($received > 0) {
                    $product = $products[$sku];
                    $product->inventoryTransactions()->make([
                        'user_id' => $users['warehouse']->id, 'type' => 'in', 'transaction_type' => 'stock_in', 'quantity' => $received,
                        'reference_type' => PurchaseOrder::class, 'reference_id' => $order->id, 'reference_document' => $order->reference_number,
                        'notes' => "Received from {$supplier->name}",
                    ])->forceFill(['created_at' => $order->received_at, 'updated_at' => $order->received_at])->save();
                }
            }

            $order->calculateTotal();
            $this->seedHistory($order, $users);
        }

        // An archived (soft-deleted) supplier that is still referenced by a historical purchase order.
        $archived = Supplier::withTrashed()->updateOrCreate(['code' => 'SUP-006'], ['name' => 'Old Town Textiles (closed)', 'contact_person' => 'Fred Go', 'email' => 'fred@oldtowntextiles.test', 'phone' => '0915-000-1111', 'payment_terms' => 'Net 30', 'lead_time_days' => 14, 'minimum_order_amount' => 8000, 'reliability_score' => 60, 'is_active' => false, 'deleted_at' => null]);
        $legacy = PurchaseOrder::create(['reference_number' => 'PO-LEGACY-0001', 'supplier_id' => $archived->id, 'created_by' => $users['staff']->id, 'approved_by' => $users['manager']->id, 'status' => 'received', 'order_date' => now()->subDays(120), 'expected_delivery_date' => now()->subDays(106), 'approved_at' => now()->subDays(120), 'sent_at' => now()->subDays(120), 'received_at' => now()->subDays(100), 'terms' => 'Net 30']);
        $legacy->items()->create(['product_id' => $products['TWL-BATH']->id, 'quantity' => 50, 'unit_price' => 300, 'received_quantity' => 50]);
        $legacy->calculateTotal();
        $this->seedHistory($legacy, $users);
        $archived->delete();
    }

    private function seedHistory(PurchaseOrder $order, $users): void
    {
        $steps = ['draft' => ['draft'], 'pending' => ['draft', 'pending'], 'rejected' => ['draft', 'pending', 'rejected'], 'approved' => ['draft', 'pending', 'approved'], 'sent' => ['draft', 'pending', 'approved', 'sent'], 'partially_received' => ['draft', 'pending', 'approved', 'sent', 'partially_received'], 'received' => ['draft', 'pending', 'approved', 'sent', 'received']][$order->status];

        $previous = null;
        foreach ($steps as $i => $status) {
            $actor = match ($status) {
                'approved', 'rejected', 'sent' => $users['manager'], 'received', 'partially_received' => $users['warehouse'], default => $users['staff']
            };
            $at = in_array($status, ['received', 'partially_received'], true) ? $order->received_at : $order->order_date->copy()->addHours($i + 1);
            $order->histories()->make(['user_id' => $actor->id, 'from_status' => $previous, 'to_status' => $status, 'remarks' => $status === 'rejected' ? $order->rejection_reason : null])
                ->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
            $previous = $status;
        }
    }
}
