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
        <label class="form-label">Room Name *</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $room->name ?? '') }}" required maxlength="150">
    </div>
    <div class="col-md-4">
        <label class="form-label">Room Code (SKU) *</label>
        <input type="text" name="sku" class="form-control"
               value="{{ old('sku', $room->sku ?? '') }}" required maxlength="80">
    </div>
    <div class="col-md-6">
        <label class="form-label">Room Type *</label>
        <select name="category" class="form-select" required>
            <option value="">Choose type</option>
            @foreach(['Standard','Deluxe','Suite','Family','Twin','Presidential'] as $category)
                <option value="{{ $category }}" @selected(old('category', $room->category ?? '') === $category)>
                    {{ $category }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Wing / Floor</label>
        <input type="text" name="supplier" class="form-control"
               value="{{ old('supplier', $room->supplier ?? '') }}" maxlength="150">
    </div>
    <div class="col-md-4">
        <label class="form-label">Available Rooms *</label>
        <input type="number" name="quantity" class="form-control"
               value="{{ old('quantity', $room->quantity ?? 0) }}" min="0" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Low Availability Alert *</label>
        <input type="number" name="reorder_level" class="form-control"
               value="{{ old('reorder_level', $room->reorder_level ?? 2) }}" min="0" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Nightly Rate *</label>
        <div class="input-group">
            <span class="input-group-text">₱</span>
            <input type="number" name="unit_price" class="form-control"
                   value="{{ old('unit_price', $room->unit_price ?? 0) }}"
                   min="0" step="0.01" required>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description', $room->description ?? '') }}</textarea>
    </div>
</div>
