<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Product,PurchaseOrder,Supplier};
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Mail\PurchaseOrderStatusMail;
use App\Notifications\PurchaseOrderStatusNotification;

class PurchaseOrderController extends Controller
{
    public function index() { Gate::authorize('viewAny', PurchaseOrder::class); return view('purchase-orders.index', ['orders'=>PurchaseOrder::with(['supplier','items.purchasable'])->latest()->paginate(12)]); }
    public function create() { Gate::authorize('create', PurchaseOrder::class); return view('purchase-orders.create', ['suppliers'=>Supplier::where('is_active',true)->orderBy('name')->get(), 'products'=>Product::where('is_active',true)->orderBy('name')->get()]); }
    public function store(Request $request) { Gate::authorize('create', PurchaseOrder::class); $data=$request->validate(['supplier_id'=>'required|exists:suppliers,id','product_id'=>'required|exists:products,id','quantity'=>'required|integer|min:1','unit_cost'=>'required|numeric|min:0','notes'=>'nullable']); $po=PurchaseOrder::create(['po_number'=>'PO-'.now()->format('YmdHis').'-'.random_int(10,99),'supplier_id'=>$data['supplier_id'],'created_by'=>auth()->id(),'status'=>'pending_approval','notes'=>$data['notes']??null]); $po->items()->create(['purchasable_type'=>Product::class,'purchasable_id'=>$data['product_id'],'quantity'=>$data['quantity'],'unit_cost'=>$data['unit_cost'],'line_total'=>$data['quantity']*$data['unit_cost']]); $po->recalculateTotals(); return redirect()->route('purchase-orders.index')->with('success','Purchase order submitted for approval.'); }
    public function show(PurchaseOrder $purchaseOrder) { Gate::authorize('view', $purchaseOrder); return view('purchase-orders.show', ['order'=>$purchaseOrder->load(['supplier','items.purchasable','creator','approver'])]); }
    public function approve(PurchaseOrder $purchaseOrder) { Gate::authorize('approve-purchase-order',$purchaseOrder); $purchaseOrder->update(['status'=>'approved','approved_by'=>auth()->id()]); $purchaseOrder->load('supplier','creator'); if($purchaseOrder->creator){$purchaseOrder->creator->notify(new PurchaseOrderStatusNotification($purchaseOrder));} if($purchaseOrder->supplier->email){Mail::to($purchaseOrder->supplier->email)->queue(new PurchaseOrderStatusMail($purchaseOrder));} return back()->with('success','Purchase order approved and notifications queued.'); }
    public function receive(PurchaseOrder $purchaseOrder) { Gate::authorize('receive-purchase-order',$purchaseOrder); $purchaseOrder->load('items','supplier','creator'); foreach($purchaseOrder->items as $item){$item->update(['received_quantity'=>$item->quantity]); if($item->purchasable instanceof Product){$item->purchasable->increment('quantity_on_hand',$item->quantity);$item->purchasable->increment('quantity',$item->quantity);$item->purchasable->inventoryTransactions()->create(['type'=>'in','transaction_type'=>'stock_in','quantity'=>$item->quantity,'quantity_before'=>$item->purchasable->quantity_on_hand-$item->quantity,'quantity_after'=>$item->purchasable->quantity_on_hand,'reference_type'=>PurchaseOrder::class,'reference_id'=>$purchaseOrder->id,'reference_document'=>$purchaseOrder->po_number,'notes'=>'Received '.$purchaseOrder->po_number]);}} $purchaseOrder->update(['status'=>'received','received_at'=>now()]); if($purchaseOrder->creator){$purchaseOrder->creator->notify(new PurchaseOrderStatusNotification($purchaseOrder));} return back()->with('success','Goods received, inventory updated, and notification queued.'); }
}
