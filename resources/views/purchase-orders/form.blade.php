@php
    $oldItems = old('items', $order->items->map(fn ($i) => ['product_id' => $i->product_id, 'quantity' => $i->quantity, 'unit_price' => $i->unit_price])->all());
    if (empty($oldItems)) { $oldItems = [['product_id' => '', 'quantity' => 1, 'unit_price' => '']]; }
@endphp
<form method="POST" action="{{ $order->exists ? route('purchase-orders.update', $order) : route('purchase-orders.store') }}" id="po-form">
    @csrf
    @if ($order->exists) @method('PUT') @endif
    <div class="card widget-card mb-3">
        <div class="card-header">Order information</div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Supplier *</label>
                <select name="supplier_id" class="form-select" required>
                    <option value="">Choose an active supplier...</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" data-lead="{{ $supplier->lead_time_days }}" data-terms="{{ $supplier->payment_terms }}" @selected(old('supplier_id', $order->supplier_id) == $supplier->id)>{{ $supplier->name }} ({{ $supplier->payment_terms }}, {{ $supplier->lead_time_days }}-day lead time)</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Order date *</label>
                <input type="date" name="order_date" class="form-control" value="{{ old('order_date', $order->order_date?->toDateString()) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Expected delivery</label>
                <input type="date" name="expected_delivery_date" class="form-control" value="{{ old('expected_delivery_date', $order->expected_delivery_date?->toDateString()) }}">
            </div>
            <div class="col-md-6"><label class="form-label">Notes</label><textarea name="notes" rows="2" class="form-control">{{ old('notes', $order->notes) }}</textarea></div>
            <div class="col-md-6"><label class="form-label">Terms</label><textarea name="terms" rows="2" class="form-control" placeholder="e.g. Net 30, deliver to receiving dock">{{ old('terms', $order->terms) }}</textarea></div>
        </div>
    </div>

    <div class="card widget-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">Line items <button type="button" class="btn btn-sm btn-light" id="add-item"><i class="bi bi-plus-lg"></i> Add item</button></div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th style="width:45%">Product</th><th>Quantity</th><th>Unit price (₱)</th><th class="text-end">Line total</th><th></th></tr></thead>
                <tbody id="items-body">
                @foreach ($oldItems as $index => $item)
                    <tr class="item-row">
                        <td>
                            <select name="items[{{ $index }}][product_id]" class="form-select form-select-sm product-select" required>
                                <option value="">Select product...</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->unit_cost }}" @selected($item['product_id'] == $product->id)>{{ $product->sku }} — {{ $product->name }} ({{ $product->quantity_on_hand }} {{ $product->unit }} on hand)</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" min="1" name="items[{{ $index }}][quantity]" class="form-control form-control-sm qty" value="{{ $item['quantity'] }}" required></td>
                        <td><input type="number" min="0" step="0.01" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm price" value="{{ $item['unit_price'] }}" required></td>
                        <td class="text-end line-total">₱0.00</td>
                        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove"><i class="bi bi-trash"></i></button></td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr><th colspan="3" class="text-end">Subtotal</th><th class="text-end" id="subtotal">₱0.00</th><th></th></tr>
                    <tr><th colspan="3" class="text-end">VAT (12%)</th><th class="text-end" id="tax">₱0.00</th><th></th></tr>
                    <tr><th colspan="3" class="text-end">Total</th><th class="text-end" id="total">₱0.00</th><th></th></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ $order->exists ? route('purchase-orders.show', $order) : route('purchase-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button name="action" value="draft" class="btn btn-outline-primary">Save as Draft</button>
        <button name="action" value="submit" class="btn btn-primary">Submit for Approval</button>
    </div>
</form>

@push('scripts')
<script>
(() => {
    const body = document.getElementById('items-body');
    const peso = n => '₱' + n.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    let nextIndex = body.querySelectorAll('.item-row').length;

    function recalc() {
        let subtotal = 0;
        body.querySelectorAll('.item-row').forEach(row => {
            const line = (parseFloat(row.querySelector('.qty').value) || 0) * (parseFloat(row.querySelector('.price').value) || 0);
            row.querySelector('.line-total').textContent = peso(line);
            subtotal += line;
        });
        const tax = Math.round(subtotal * 0.12 * 100) / 100;
        document.getElementById('subtotal').textContent = peso(subtotal);
        document.getElementById('tax').textContent = peso(tax);
        document.getElementById('total').textContent = peso(subtotal + tax);
    }

    document.getElementById('add-item').addEventListener('click', () => {
        const row = body.querySelector('.item-row').cloneNode(true);
        row.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/items\[\d+\]/, `items[${nextIndex}]`);
            el.value = el.classList.contains('qty') ? 1 : '';
        });
        nextIndex++;
        body.appendChild(row);
        recalc();
    });

    body.addEventListener('click', e => {
        const button = e.target.closest('.remove-item');
        if (button && body.querySelectorAll('.item-row').length > 1) {
            button.closest('.item-row').remove();
            recalc();
        }
    });

    body.addEventListener('change', e => {
        if (e.target.classList.contains('product-select')) {
            const price = e.target.selectedOptions[0]?.dataset.price;
            const priceInput = e.target.closest('.item-row').querySelector('.price');
            if (price && !priceInput.value) priceInput.value = price;
        }
        recalc();
    });
    body.addEventListener('input', recalc);
    recalc();
})();
</script>
@endpush
