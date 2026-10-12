<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Bath Towels',
                'sku' => 'HSK-001',
                'description' => 'Premium bath towels for hotel rooms',
                'category' => 'Housekeeping',
                'quantity' => 45,
                'reorder_level' => 20,
                'unit_price' => 350.00,
                'supplier' => 'CleanPro',
            ],
            [
                'name' => 'Bed Sheets',
                'sku' => 'HSK-002',
                'description' => 'High-quality bed sheets',
                'category' => 'Housekeeping',
                'quantity' => 28,
                'reorder_level' => 15,
                'unit_price' => 650.00,
                'supplier' => 'LinenHub',
            ],
            [
                'name' => 'Shampoo',
                'sku' => 'HTL-001',
                'description' => 'Hotel-grade shampoo bottles',
                'category' => 'Toiletries',
                'quantity' => 18,
                'reorder_level' => 25,
                'unit_price' => 95.00,
                'supplier' => 'FreshCare',
            ],
            [
                'name' => 'Soap',
                'sku' => 'HTL-002',
                'description' => 'Premium soap bars',
                'category' => 'Toiletries',
                'quantity' => 60,
                'reorder_level' => 30,
                'unit_price' => 45.00,
                'supplier' => 'FreshCare',
            ],
            [
                'name' => 'Bottled Water',
                'sku' => 'FNB-001',
                'description' => 'Mineral water bottles',
                'category' => 'Food & Beverage',
                'quantity' => 90,
                'reorder_level' => 40,
                'unit_price' => 25.00,
                'supplier' => 'AquaPure',
            ],
            [
                'name' => 'Coffee Beans',
                'sku' => 'FNB-002',
                'description' => 'Premium coffee beans',
                'category' => 'Food & Beverage',
                'quantity' => 14,
                'reorder_level' => 20,
                'unit_price' => 480.00,
                'supplier' => 'BeanWorks',
            ],
            [
                'name' => 'Tea Bags',
                'sku' => 'FNB-003',
                'description' => 'Assorted tea bags',
                'category' => 'Food & Beverage',
                'quantity' => 55,
                'reorder_level' => 20,
                'unit_price' => 160.00,
                'supplier' => 'TeaHouse',
            ],
            [
                'name' => 'LED Bulbs',
                'sku' => 'MNT-001',
                'description' => 'Energy-efficient LED bulbs',
                'category' => 'Maintenance',
                'quantity' => 12,
                'reorder_level' => 15,
                'unit_price' => 120.00,
                'supplier' => 'BrightTech',
            ],
            [
                'name' => 'Cleaning Gloves',
                'sku' => 'MNT-002',
                'description' => 'Heavy-duty cleaning gloves',
                'category' => 'Maintenance',
                'quantity' => 35,
                'reorder_level' => 20,
                'unit_price' => 75.00,
                'supplier' => 'SafeHands',
            ],
            [
                'name' => 'Aircon Filter',
                'sku' => 'MNT-003',
                'description' => 'Air conditioning filters',
                'category' => 'Maintenance',
                'quantity' => 7,
                'reorder_level' => 10,
                'unit_price' => 450.00,
                'supplier' => 'CoolAir',
            ],
            [
                'name' => 'Laundry Detergent',
                'sku' => 'LND-001',
                'description' => 'Commercial laundry detergent',
                'category' => 'Laundry',
                'quantity' => 22,
                'reorder_level' => 15,
                'unit_price' => 280.00,
                'supplier' => 'WashPro',
            ],
            [
                'name' => 'Fabric Softener',
                'sku' => 'LND-002',
                'description' => 'Premium fabric softener',
                'category' => 'Laundry',
                'quantity' => 9,
                'reorder_level' => 12,
                'unit_price' => 310.00,
                'supplier' => 'WashPro',
            ],
            [
                'name' => 'Pillow Cases',
                'sku' => 'HSK-003',
                'description' => 'Soft pillow cases',
                'category' => 'Housekeeping',
                'quantity' => 8,
                'reorder_level' => 12,
                'unit_price' => 180.00,
                'supplier' => 'LinenHub',
            ],
            [
                'name' => 'Toothpaste',
                'sku' => 'HTL-003',
                'description' => 'Travel-size toothpaste',
                'category' => 'Toiletries',
                'quantity' => 0,
                'reorder_level' => 10,
                'unit_price' => 60.00,
                'supplier' => 'FreshCare',
            ],
            [
                'name' => 'Canned Juice',
                'sku' => 'FNB-004',
                'description' => 'Assorted canned juices',
                'category' => 'Food & Beverage',
                'quantity' => 6,
                'reorder_level' => 10,
                'unit_price' => 55.00,
                'supplier' => 'DrinkCo',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}