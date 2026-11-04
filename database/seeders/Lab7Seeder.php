<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Product,Supplier,User};
use Illuminate\Support\Carbon;

class Lab7Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(['email'=>'manager@example.com'],['name'=>'Lab Manager','password'=>'password','role'=>'manager']);
        $supplier=Supplier::updateOrCreate(['code'=>'SUP-001'],['name'=>'BuildRight Hardware Supply','contact_person'=>'Ana Santos','email'=>'orders@buildright.test','phone'=>'0917-555-0101','lead_time_days'=>5,'reliability_score'=>96]);
        $product=Product::updateOrCreate(['sku'=>'CEM-40KG'],['name'=>'Portland Cement 40kg','description'=>'Construction cement','category'=>'Construction Materials','unit'=>'bag','quantity_on_hand'=>18,'quantity'=>18,'reorder_level'=>30,'safety_stock'=>10,'unit_cost'=>285,'unit_price'=>285]);
        $product->suppliers()->syncWithoutDetaching([$supplier->id=>['unit_cost'=>275,'minimum_order_quantity'=>10,'is_preferred'=>true]]);
        $product->inventoryTransactions()->delete();
        foreach (range(1, 20) as $day) { $product->inventoryTransactions()->create(['type'=>'out','quantity'=>3,'notes'=>'Seeded historical usage','created_at'=>Carbon::now()->subDays($day),'updated_at'=>Carbon::now()->subDays($day)]); }
        $rebar=Product::updateOrCreate(['sku'=>'REB-10MM'],['name'=>'Steel Rebar 10mm','description'=>'Steel reinforcing bar','category'=>'Construction Materials','unit'=>'length','quantity_on_hand'=>120,'quantity'=>120,'reorder_level'=>100,'safety_stock'=>20,'unit_cost'=>245,'unit_price'=>245]);
        $rebar->suppliers()->syncWithoutDetaching([$supplier->id=>['unit_cost'=>235,'minimum_order_quantity'=>20,'is_preferred'=>true]]);
    }
}
