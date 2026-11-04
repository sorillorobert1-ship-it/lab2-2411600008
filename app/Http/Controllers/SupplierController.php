<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class SupplierController extends Controller
{
    public function index() { Gate::authorize('manage-suppliers'); return view('suppliers.index', ['suppliers'=>Supplier::withCount('products')->withSum('purchaseOrders','total')->latest()->paginate(10)]); }
    public function create() { Gate::authorize('manage-suppliers'); return view('suppliers.create'); }
    public function store(Request $request) { Gate::authorize('manage-suppliers'); $data=$request->validate(['code'=>'required|alpha_dash|unique:suppliers,code','name'=>'required|max:150','email'=>'nullable|email','lead_time_days'=>'required|integer|min:1','contact_person'=>'nullable|max:100','phone'=>'nullable|max:50','address'=>'nullable']); Supplier::create($data); return redirect()->route('suppliers.index')->with('success','Supplier created successfully.'); }
    public function show(Supplier $supplier) { Gate::authorize('manage-suppliers'); $supplier->load('products','purchaseOrders'); $performance=Cache::remember('supplier.performance.'.$supplier->id, now()->addMinutes(10), fn () => ['orders'=>$supplier->purchaseOrders->count(),'completed'=>$supplier->purchaseOrders->where('status','received')->count(),'spend'=>$supplier->purchaseOrders->sum('total'),'on_time'=>$supplier->purchaseOrders->where('status','received')->filter(fn($po)=>$po->received_at && $po->expected_delivery_date && $po->received_at->lte($po->expected_delivery_date))->count()]); return view('suppliers.show', compact('supplier','performance')); }
    public function edit(Supplier $supplier) { Gate::authorize('manage-suppliers'); return view('suppliers.edit', compact('supplier')); }
    public function update(Request $request, Supplier $supplier) { Gate::authorize('manage-suppliers'); $data=$request->validate(['code'=>'required|alpha_dash|unique:suppliers,code,'.$supplier->id,'name'=>'required|max:150','email'=>'nullable|email','lead_time_days'=>'required|integer|min:1','contact_person'=>'nullable|max:100','phone'=>'nullable|max:50','address'=>'nullable']); $supplier->update($data); return redirect()->route('suppliers.index')->with('success','Supplier updated.'); }
    public function destroy(Supplier $supplier) { Gate::authorize('manage-suppliers'); $supplier->delete(); return redirect()->route('suppliers.index')->with('success','Supplier archived using soft delete.'); }
}
