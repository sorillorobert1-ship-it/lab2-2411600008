<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierPerformanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    private const SORTABLE = ['name', 'lead_time_days', 'reliability_score', 'created_at'];

    public function index(Request $request)
    {
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'name';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $suppliers = Supplier::query()
            ->withCount('products')
            ->withCount(['purchaseOrders as pending_orders_count' => fn ($q) => $q->active()])
            ->search($request->query('search'))
            ->when($request->query('status') === 'active', fn ($q) => $q->active())
            ->when($request->query('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->query('status') === 'archived', fn ($q) => $q->onlyTrashed())
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'sort', 'direction'));
    }

    public function create()
    {
        $this->authorize('manage-suppliers');

        return view('suppliers.create', ['supplier' => new Supplier(['lead_time_days' => 7, 'payment_terms' => 'Net 30', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage-suppliers');

        $supplier = Supplier::create($this->validated($request));

        return redirect()->route('suppliers.show', $supplier)->with('success', "Supplier {$supplier->name} created successfully.");
    }

    public function show(Supplier $supplier, SupplierPerformanceService $performanceService)
    {
        $supplier->load([
            'products',
            'catalogProducts',
            'purchaseOrders' => fn ($q) => $q->with('items')->latest('order_date'),
        ]);

        return view('suppliers.show', [
            'supplier' => $supplier,
            'performance' => $performanceService->calculatePerformance($supplier),
            'recentOrders' => $supplier->purchaseOrders->take(10),
        ]);
    }

    public function edit(Supplier $supplier)
    {
        $this->authorize('manage-suppliers');

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorize('manage-suppliers');

        $supplier->update($this->validated($request, $supplier));

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('manage-suppliers');

        if ($supplier->hasPendingPurchaseOrders()) {
            return back()->with('error', "{$supplier->name} still has open purchase orders. Complete or cancel them before deleting this supplier.");
        }

        $supplier->delete();
        SupplierPerformanceService::forget();

        return redirect()->route('suppliers.index')->with('success', "{$supplier->name} was archived (soft deleted). Historical purchase orders still reference it.");
    }

    public function restore(int $id)
    {
        $this->authorize('manage-suppliers');

        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->restore();

        return redirect()->route('suppliers.show', $supplier)->with('success', "{$supplier->name} was restored.");
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'alpha_dash', 'max:20', Rule::unique('suppliers', 'code')->ignore($supplier)],
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('suppliers', 'email')->ignore($supplier)],
            'phone' => ['required', 'regex:/^(\+63|0)9\d{2}[- ]?\d{3}[- ]?\d{4}$|^\(?0\d{1,2}\)?[- ]?\d{3,4}[- ]?\d{4}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'payment_terms' => ['required', Rule::in(Supplier::PAYMENT_TERMS)],
            'lead_time_days' => ['required', 'integer', 'min:1', 'max:120'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'reliability_score' => ['nullable', 'numeric', 'between:0,100'],
        ], [
            'phone.regex' => 'Enter a valid Philippine phone number, e.g. 0917-555-0101 or (02) 8123-4567.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
