@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Product Name *</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $product->name ?? '') }}" required maxlength="150">
    </div>

    <div class="col-md-4">
        <label class="form-label">SKU *</label>
        <input type="text" name="sku" class="form-control"
               value="{{ old('sku', $product->sku ?? '') }}" required maxlength="80">
    </div>

    <div class="col-md-6">
        <label class="form-label">Category *</label>
        <select name="category" class="form-select" required>
            <option value="">Choose category</option>
            @foreach(['Lumber','Hardware','Tools','Electrical','Plumbing','Paint'] as $category)
                <option value="{{ $category }}"
                    @selected(old('category', $product->category ?? '') === $category)>
                    {{ $category }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Supplier</label>
        <input type="text" name="supplier" class="form-control"
               value="{{ old('supplier', $product->supplier ?? '') }}" maxlength="150">
    </div>

    <div class="col-md-4">
        <label class="form-label">Quantity *</label>
        <input type="number" name="quantity" class="form-control"
               value="{{ old('quantity', $product->quantity ?? 0) }}" min="0" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Reorder Level *</label>
        <input type="number" name="reorder_level" class="form-control"
               value="{{ old('reorder_level', $product->reorder_level ?? 10) }}" min="0" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Unit Price *</label>
        <div class="input-group">
            <span class="input-group-text">₱</span>
            <input type="number" name="unit_price" class="form-control"
                   value="{{ old('unit_price', $product->unit_price ?? 0) }}"
                   min="0" step="0.01" required>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
</div>
