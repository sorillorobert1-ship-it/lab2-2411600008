<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Http\Resources\InventoryTransactionResource;
use App\Http\Resources\ProductResource;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryTransaction::with(['product', 'user'])->latest('created_at');
        if ($request->filled('product_id')) $query->where('product_id', $request->integer('product_id'));
        if ($request->filled('transaction_type')) $query->where('transaction_type', $request->string('transaction_type'));
        if ($request->filled('from')) $query->whereDate('created_at', '>=', $request->date('from'));
        if ($request->filled('to')) $query->whereDate('created_at', '<=', $request->date('to'));

        return view('inventory.index', [
            'products' => Product::orderBy('name')->get(),
            'transactions' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function store(Request $request, InventoryService $service)
    {
        $data = $request->validate(['product_id' => 'required|exists:products,id', 'transaction_type' => 'required|in:stock_in,stock_out', 'quantity' => 'required|integer|min:1', 'reference_document' => 'nullable|string|max:150', 'notes' => 'nullable|string|max:1000']);
        $service->move((int) $data['product_id'], (int) $request->user()->id, $data['transaction_type'], (int) $data['quantity'], $data['reference_document'] ?? null, $data['notes'] ?? null);
        return redirect()->route('inventory.index')->with('success', 'Stock movement saved and logged.');
    }

    public function adjust(Request $request, InventoryService $service)
    {
        $data = $request->validate(['product_id' => 'required|exists:products,id', 'physical_quantity' => 'required|integer|min:0', 'reason' => 'required|string|max:100', 'notes' => 'nullable|string|max:1000']);
        $service->adjust((int) $data['product_id'], (int) $request->user()->id, (int) $data['physical_quantity'], $data['reason'], $data['notes'] ?? null);
        return redirect()->route('inventory.index')->with('success', 'Stock adjustment saved and logged.');
    }

    public function api()
    {
        return response()->json([
            'updated_at' => now()->toIso8601String(),
            'total_products' => Product::count(),
            'total_quantity' => Product::sum('quantity'),
            'low_stock' => Product::lowStock()->count(),
            'inventory_value' => Product::selectRaw('COALESCE(SUM(quantity * unit_price),0) as total')->value('total'),
            'products' => ProductResource::collection(Product::orderBy('name')->get()),
            'recent_transactions' => InventoryTransactionResource::collection(InventoryTransaction::with(['product', 'user'])->latest('id')->limit(5)->get()),
        ]);
    }

    public function dashboardStats(){return response()->json(['total_products'=>Product::count(),'total_quantity'=>Product::sum('quantity'),'low_stock'=>Product::lowStock()->count(),'inventory_value'=>Product::selectRaw('COALESCE(SUM(quantity * unit_price),0) as total')->value('total'),'updated_at'=>now()->toIso8601String()]);}
    public function lowStockProducts(){return ProductResource::collection(Product::lowStock()->orderBy('name')->get());}
    public function recentTransactions(){return InventoryTransactionResource::collection(InventoryTransaction::with(['product','user'])->recent()->limit(20)->get());}
    public function notifications(Request $request){return response()->json($request->user()->unreadNotifications()->latest()->limit(15)->get());}
    public function markRead(Request $request){$request->user()->unreadNotifications->markAsRead();return back();}
    public function markNotificationRead(Request $request, string $id){$notification=$request->user()->notifications()->whereKey($id)->firstOrFail();$notification->markAsRead();return back();}

    public function export(): StreamedResponse
    {
        $transactions = InventoryTransaction::with(['product', 'user'])->latest('created_at')->get();
        return response()->streamDownload(function () use ($transactions) { $out=fopen('php://output','w'); fputcsv($out,['Date','Product','Type','Quantity','Before','After','User','Reference','Notes']); foreach($transactions as $tx) fputcsv($out,[$tx->created_at,$tx->product?->name,$tx->transaction_type,$tx->quantity,$tx->quantity_before,$tx->quantity_after,$tx->user?->name,$tx->reference_document,$tx->notes]); fclose($out); }, 'inventory-transactions.csv', ['Content-Type'=>'text/csv']);
    }
}
