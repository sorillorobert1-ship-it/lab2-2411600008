@extends('layouts.app')
@section('title', 'Record Transaction')
@section('content')
<h1 class="h2 fw-bold mb-4">Record Inventory Transaction</h1>
@if($errors->any())<div class="alert alert-danger">{{ implode(' ', $errors->all()) }}</div>@endif
<form method="POST" action="{{ route('transactions.store') }}" class="card shadow-sm p-4" id="transaction-form">@csrf
    <div class="mb-3"><label class="form-label">Product</label><select name="product_id" id="transaction-product" class="form-select" required><option value="">Choose product</option>@foreach($products as $product)<option value="{{ $product->id }}" data-quantity="{{ $product->quantity }}">{{ $product->name }} ({{ $product->quantity }} available)</option>@endforeach</select><div id="current-stock" class="form-text"></div></div>
    <div class="mb-3"><label class="form-label">Transaction type</label><select name="transaction_type" id="transaction-type" class="form-select" required><option value="stock_in">Stock In</option><option value="stock_out">Stock Out</option></select></div>
    <div class="mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" id="transaction-quantity" min="1" class="form-control" required><div id="quantity-help" class="form-text"></div></div>
    <div class="mb-3"><label class="form-label">Reference number</label><input name="reference_document" class="form-control"></div><div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
    <div><button class="btn btn-primary">Save Transaction</button> <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
</form>
<script>
const productSelect=document.getElementById('transaction-product'), typeSelect=document.getElementById('transaction-type'), quantityInput=document.getElementById('transaction-quantity'), stockText=document.getElementById('current-stock'), helpText=document.getElementById('quantity-help');
function syncQuantityLimit(){const option=productSelect.selectedOptions[0], current=option?.dataset.quantity; stockText.textContent=current === undefined ? '' : `Current stock: ${current}`; if(typeSelect.value==='stock_out' && current !== undefined){quantityInput.max=current; helpText.textContent=`Stock out cannot exceed ${current}.`;}else{quantityInput.removeAttribute('max'); helpText.textContent='';} if(quantityInput.value && quantityInput.max && Number(quantityInput.value)>Number(quantityInput.max)) quantityInput.value=quantityInput.max;}
productSelect.addEventListener('change',syncQuantityLimit); typeSelect.addEventListener('change',syncQuantityLimit); syncQuantityLimit();
</script>
@endsection
