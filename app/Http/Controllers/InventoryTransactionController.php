<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = InventoryTransaction::with(['product', 'user'])->latest('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->string('transaction_type'));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        return view('transactions.index', [
            'products' => Product::orderBy('name')->get(),
            'transactions' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('transactions.create', ['products' => Product::orderBy('name')->get()]);
    }

    public function store(Request $request, InventoryService $service): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'transaction_type' => ['required', 'in:stock_in,stock_out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference_document' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->move($data['product_id'], $request->user()->id, $data['transaction_type'], $data['quantity'], $data['reference_document'] ?? null, $data['notes'] ?? null);

        return redirect()->route('transactions.index')->with('success', 'Transaction saved successfully.');
    }

    public function show(InventoryTransaction $transaction): View
    {
        $transaction->load(['product', 'user']);
        return view('transactions.show', compact('transaction'));
    }

    public function destroy(Request $request, InventoryTransaction $transaction): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);
        return back()->with('error', 'Inventory transactions are immutable audit records and cannot be deleted.');
    }
}
