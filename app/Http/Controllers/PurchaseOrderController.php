<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\InventoryService;
use App\Services\SearchService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $filters = $request->only(['search', 'status', 'supplier_id', 'date_from', 'date_to']);

        return view('purchase-orders.index', [
            'orders' => $search->purchaseOrders($filters)->paginate(10)->withQueryString(),
            'suppliers' => Supplier::withTrashed()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $this->authorize('create', PurchaseOrder::class);

        return view('purchase-orders.create', $this->formData(new PurchaseOrder([
            'order_date' => now(),
            'expected_delivery_date' => now()->addDays(7),
        ])));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PurchaseOrder::class);

        $data = $this->validated($request);

        $order = DB::transaction(function () use ($data, $request) {
            $order = PurchaseOrder::create([
                'reference_number' => PurchaseOrder::generateReferenceNumber(),
                'supplier_id' => $data['supplier_id'],
                'created_by' => $request->user()->id,
                'status' => $request->input('action') === 'submit' ? 'pending' : 'draft',
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);

            $this->syncItems($order, $data['items']);

            return $order;
        });

        return redirect()->route('purchase-orders.show', $order)->with('success', "Purchase order {$order->reference_number} saved as {$order->status_label}.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('view', $purchaseOrder);

        return view('purchase-orders.show', [
            'order' => $purchaseOrder->load(['supplier', 'items.product', 'createdBy', 'approvedBy', 'histories.user']),
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder);

        return view('purchase-orders.edit', $this->formData($purchaseOrder->load('items')));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder);

        $data = $this->validated($request);

        DB::transaction(function () use ($purchaseOrder, $data, $request) {
            $purchaseOrder->items()->delete();
            $purchaseOrder->fill([
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);
            $this->syncItems($purchaseOrder, $data['items']);

            if ($request->input('action') === 'submit') {
                $purchaseOrder->update(['status' => 'pending']);
            }
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->canBeEdited()) {
            $this->authorize('delete', $purchaseOrder);
            $purchaseOrder->delete();

            return redirect()->route('purchase-orders.index')->with('success', "Draft {$purchaseOrder->reference_number} deleted.");
        }

        $this->authorize('cancel', $purchaseOrder);
        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', "{$purchaseOrder->reference_number} was cancelled.");
    }

    public function submit(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('submit', $purchaseOrder);

        $purchaseOrder->update(['status' => 'pending']);

        return back()->with('success', 'Purchase order submitted for approval. Managers were notified by email.');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('approve', $purchaseOrder);

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase order approved. The creator was notified.');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('reject', $purchaseOrder);

        $data = $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $purchaseOrder->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return back()->with('success', 'Purchase order rejected.');
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('send', $purchaseOrder);

        $purchaseOrder->update(['status' => 'sent', 'sent_at' => now()]);

        $message = $purchaseOrder->supplier->email
            ? "Purchase order emailed to {$purchaseOrder->supplier->email}."
            : 'Purchase order marked as sent (supplier has no email configured).';

        return back()->with('success', $message);
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder, InventoryService $inventory)
    {
        $this->authorize('receive', $purchaseOrder);

        $purchaseOrder->load('items.product');

        $data = $request->validate([
            'received' => 'required|array',
            'received.*' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($purchaseOrder, $data, $inventory, $request) {
            foreach ($purchaseOrder->items as $item) {
                $quantity = min((int) ($data['received'][$item->id] ?? 0), $item->outstanding_quantity);

                if ($quantity < 1) {
                    continue;
                }

                $item->increment('received_quantity', $quantity);

                $inventory->move(
                    $item->product_id,
                    $request->user()->id,
                    'stock_in',
                    $quantity,
                    $purchaseOrder->reference_number,
                    "Received from {$purchaseOrder->supplier->name}",
                    $purchaseOrder
                );
            }

            $fullyReceived = $purchaseOrder->items()->whereColumn('received_quantity', '<', 'quantity')->doesntExist();

            $purchaseOrder->update([
                'status' => $fullyReceived ? 'received' : 'partially_received',
                'received_at' => now(),
            ]);
        });

        return back()->with('success', 'Delivery recorded and inventory transactions created.');
    }

    public function pdf(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('view', $purchaseOrder);

        return Pdf::loadView('pdf.purchase-order', ['order' => $purchaseOrder->load('supplier', 'items.product', 'createdBy', 'approvedBy')])
            ->download($purchaseOrder->reference_number.'.pdf');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'supplier_id.exists' => 'Please choose an active supplier.',
            'items.required' => 'Add at least one line item.',
            'items.*.product_id.distinct' => 'Each product can only appear once per purchase order.',
        ]);
    }

    private function syncItems(PurchaseOrder $order, array $items): void
    {
        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        $order->unsetRelation('items');
        $order->calculateTotal();
    }

    private function formData(PurchaseOrder $order): array
    {
        return [
            'order' => $order,
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'products' => Product::active()->orderBy('name')->get(),
        ];
    }
}
