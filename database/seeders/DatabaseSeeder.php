<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed a small, repeatable dataset for the Lab 6 inventory demonstration.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Inventory Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        $products = [
            ['name' => 'Queen Bed Linen Set', 'sku' => 'HTL-LINEN-QUEEN', 'description' => 'Fresh queen-size bed sheet and pillowcase set for guest rooms.', 'category' => 'Room Linens', 'quantity' => 28, 'reorder_level' => 10, 'unit_price' => 1250.00, 'supplier' => 'Robert Hotel Linen Supply'],
            ['name' => 'Bath Towel Set', 'sku' => 'HTL-TOWEL-BATH', 'description' => 'Soft bath towel set for daily room turnover.', 'category' => 'Guest Amenities', 'quantity' => 42, 'reorder_level' => 20, 'unit_price' => 480.00, 'supplier' => 'Robert Hotel Linen Supply'],
            ['name' => 'Guest Toiletry Kit', 'sku' => 'HTL-TOILETRY-KIT', 'description' => 'Shampoo, soap, conditioner, and dental kit for hotel guests.', 'category' => 'Guest Amenities', 'quantity' => 18, 'reorder_level' => 25, 'unit_price' => 85.00, 'supplier' => 'Hospitality Essentials PH'],
            ['name' => 'Bottled Water 500ml', 'sku' => 'HTL-WATER-500ML', 'description' => 'Complimentary bottled water for occupied guest rooms.', 'category' => 'Guest Amenities', 'quantity' => 96, 'reorder_level' => 40, 'unit_price' => 18.00, 'supplier' => 'ClearSpring Beverages'],
            ['name' => 'Laundry Detergent 5L', 'sku' => 'HTL-LAUNDRY-5L', 'description' => 'Commercial detergent for housekeeping and laundry operations.', 'category' => 'Housekeeping', 'quantity' => 9, 'reorder_level' => 6, 'unit_price' => 620.00, 'supplier' => 'CleanStay Supplies'],
            ['name' => 'All Purpose Cleaner 1L', 'sku' => 'HTL-CLEANER-1L', 'description' => 'All-purpose cleaning solution for rooms and common areas.', 'category' => 'Housekeeping', 'quantity' => 14, 'reorder_level' => 15, 'unit_price' => 145.00, 'supplier' => 'CleanStay Supplies'],
            ['name' => 'Pillow Protector', 'sku' => 'HTL-PILLOW-PROTECTOR', 'description' => 'Washable pillow protector for guest room maintenance.', 'category' => 'Room Linens', 'quantity' => 32, 'reorder_level' => 12, 'unit_price' => 220.00, 'supplier' => 'Robert Hotel Linen Supply'],
            ['name' => 'Room Slippers Pair', 'sku' => 'HTL-SLIPPERS-PAIR', 'description' => 'Disposable guest room slippers supplied per occupied room.', 'category' => 'Guest Amenities', 'quantity' => 20, 'reorder_level' => 30, 'unit_price' => 35.00, 'supplier' => 'Hospitality Essentials PH'],
        ];

        // Convert the demo hardware records created by the previous version
        // of this seeder into the Robert Hotel supply dataset.
        $legacySkus = [
            'CEM-40KG', 'REB-10MM', 'CHB-4IN', 'PLY-12-4X8',
            'NAIL-CMN-2IN', 'SCR-ST-1IN', 'PVC-12-3M', 'PNT-LTX-4L',
            'HTL-LINEN-QUEEN', 'HTL-TOWEL-BATH', 'HTL-TOILETRY-KIT',
            'HTL-WATER-500ML', 'HTL-LAUNDRY-5L', 'HTL-CLEANER-1L',
            'HTL-PILLOW-PROTECTOR', 'HTL-SLIPPERS-PAIR',
        ];

        Product::whereIn('sku', $legacySkus)
            ->orderBy('id')
            ->get()
            ->values()
            ->each(function (Product $legacyProduct, int $index) use ($products): void {
                if (isset($products[$index])) {
                    $legacyProduct->update($products[$index]);
                }
            });

        foreach ($products as $data) {
            $initialQuantity = $data['quantity'];
            $data['quantity'] = 0;

            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                $data
            );

            if ($product->wasRecentlyCreated && $initialQuantity > 0) {
                $product->update(['quantity' => $initialQuantity]);

                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'transaction_type' => 'stock_in',
                    'quantity' => $initialQuantity,
                    'reference_document' => 'SEED-LAB6',
                    'quantity_before' => 0,
                    'quantity_after' => $initialQuantity,
                    'notes' => 'Initial stock from the Lab 6 demo seeder.',
                ]);
            }
        }

        $this->command?->info('Lab 6 demo data seeded.');
        $this->command?->info('Login: admin@example.com / password');
    }
}
