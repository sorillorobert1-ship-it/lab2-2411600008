<?php
namespace App\Http\Controllers;

use App\Models\{InventoryTransaction,Product,User};
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index() { return view('inventory.index', ['products'=>Product::orderBy('name')->get(), 'transactions'=>InventoryTransaction::with(['product','user'])->latest('created_at')->paginate(20)]); }
    public function store(Request $request, InventoryService $service) { $data=$request->validate(['product_id'=>'required|exists:products,id','transaction_type'=>'required|in:stock_in,stock_out','quantity'=>'required|integer|min:1','reference_document'=>'nullable|max:150','notes'=>'nullable|max:1000']); $user=$request->user() ?: User::firstOrCreate(['email'=>'manager@example.com'],['name'=>'Lab Manager','password'=>'password','role'=>'manager']); $service->move((int)$data['product_id'],$user->id,$data['transaction_type'],(int)$data['quantity'],$data['reference_document']??null,$data['notes']??null); return back()->with('success','Stock movement saved and low-stock checks dispatched.'); }
    public function api() { return response()->json(['updated_at'=>now()->toIso8601String(),'total_products'=>Product::count(),'total_quantity'=>Product::sum('quantity_on_hand'),'low_stock'=>Product::lowStock()->count(),'inventory_value'=>Product::selectRaw('COALESCE(SUM(quantity_on_hand * unit_cost),0) as total')->value('total'),'products'=>Product::orderBy('name')->get(),'recent_transactions'=>InventoryTransaction::with('product')->latest('created_at')->limit(5)->get()]); }
}
