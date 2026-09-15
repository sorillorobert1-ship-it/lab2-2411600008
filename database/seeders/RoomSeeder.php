<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['name' => 'Garden Standard 101', 'sku' => 'STD-101', 'description' => 'Cozy garden-view standard room with queen bed.', 'category' => 'Standard', 'quantity' => 8, 'reorder_level' => 3, 'unit_price' => 2800.00, 'supplier' => 'Wing A / Floor 1'],
            ['name' => 'Garden Standard 102', 'sku' => 'STD-102', 'description' => 'Standard twin room facing the courtyard.', 'category' => 'Standard', 'quantity' => 6, 'reorder_level' => 3, 'unit_price' => 2650.00, 'supplier' => 'Wing A / Floor 1'],
            ['name' => 'City Deluxe 201', 'sku' => 'DLX-201', 'description' => 'Deluxe king room with city skyline view.', 'category' => 'Deluxe', 'quantity' => 5, 'reorder_level' => 2, 'unit_price' => 4500.00, 'supplier' => 'Wing B / Floor 2'],
            ['name' => 'City Deluxe 202', 'sku' => 'DLX-202', 'description' => 'Deluxe room with work desk and rain shower.', 'category' => 'Deluxe', 'quantity' => 2, 'reorder_level' => 2, 'unit_price' => 4700.00, 'supplier' => 'Wing B / Floor 2'],
            ['name' => 'Pool Deluxe 210', 'sku' => 'DLX-210', 'description' => 'Pool-view deluxe room with balcony.', 'category' => 'Deluxe', 'quantity' => 4, 'reorder_level' => 2, 'unit_price' => 5200.00, 'supplier' => 'Wing B / Floor 2'],
            ['name' => 'Ocean Suite 301', 'sku' => 'SUI-301', 'description' => 'One-bedroom suite with living area and ocean view.', 'category' => 'Suite', 'quantity' => 3, 'reorder_level' => 1, 'unit_price' => 8900.00, 'supplier' => 'Wing C / Floor 3'],
            ['name' => 'Ocean Suite 302', 'sku' => 'SUI-302', 'description' => 'Executive suite with lounge access.', 'category' => 'Suite', 'quantity' => 1, 'reorder_level' => 1, 'unit_price' => 9800.00, 'supplier' => 'Wing C / Floor 3'],
            ['name' => 'Executive Suite 310', 'sku' => 'SUI-310', 'description' => 'Corner suite with two bathrooms.', 'category' => 'Suite', 'quantity' => 0, 'reorder_level' => 1, 'unit_price' => 10500.00, 'supplier' => 'Wing C / Floor 3'],
            ['name' => 'Family Connecting 401', 'sku' => 'FAM-401', 'description' => 'Two connecting rooms for families.', 'category' => 'Family', 'quantity' => 4, 'reorder_level' => 2, 'unit_price' => 7200.00, 'supplier' => 'Wing D / Floor 4'],
            ['name' => 'Family Connecting 402', 'sku' => 'FAM-402', 'description' => 'Family suite with sofa bed and crib option.', 'category' => 'Family', 'quantity' => 2, 'reorder_level' => 2, 'unit_price' => 7500.00, 'supplier' => 'Wing D / Floor 4'],
            ['name' => 'Family Villa 410', 'sku' => 'FAM-410', 'description' => 'Ground-level family villa with kitchenette.', 'category' => 'Family', 'quantity' => 3, 'reorder_level' => 1, 'unit_price' => 8800.00, 'supplier' => 'Garden Annex'],
            ['name' => 'Twin Business 210', 'sku' => 'TWN-210', 'description' => 'Twin beds, ideal for business travelers.', 'category' => 'Twin', 'quantity' => 7, 'reorder_level' => 3, 'unit_price' => 3200.00, 'supplier' => 'Wing B / Floor 2'],
            ['name' => 'Twin Business 211', 'sku' => 'TWN-211', 'description' => 'Twin room with extra work space.', 'category' => 'Twin', 'quantity' => 1, 'reorder_level' => 3, 'unit_price' => 3350.00, 'supplier' => 'Wing B / Floor 2'],
            ['name' => 'Twin Economy 112', 'sku' => 'TWN-112', 'description' => 'Compact twin room near the lobby.', 'category' => 'Twin', 'quantity' => 9, 'reorder_level' => 4, 'unit_price' => 2400.00, 'supplier' => 'Wing A / Floor 1'],
            ['name' => 'Presidential Penthouse', 'sku' => 'PRE-501', 'description' => 'Top-floor presidential suite with private terrace.', 'category' => 'Presidential', 'quantity' => 0, 'reorder_level' => 1, 'unit_price' => 25000.00, 'supplier' => 'Penthouse / Floor 5'],
            ['name' => 'Presidential Harbor', 'sku' => 'PRE-502', 'description' => 'Harbor-view presidential suite with butler service.', 'category' => 'Presidential', 'quantity' => 1, 'reorder_level' => 1, 'unit_price' => 28500.00, 'supplier' => 'Penthouse / Floor 5'],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(
                ['sku' => $room['sku']],
                $room
            );
        }
    }
}
