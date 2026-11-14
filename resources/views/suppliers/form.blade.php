<form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="card widget-card">
    @csrf
    @if ($supplier->exists) @method('PUT') @endif
    <div class="card-body row g-3">
        <div class="col-md-3">
            <label class="form-label">Supplier code *</label>
            <input name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $supplier->code) }}" placeholder="SUP-006" required>
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-9">
            <label class="form-label">Company name *</label>
            <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $supplier->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Contact person *</label>
            <input name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person', $supplier->contact_person) }}" required>
            @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $supplier->email) }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Phone *</label>
            <input name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $supplier->phone) }}" placeholder="0917-555-0101" required>
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label">Physical address</label>
            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $supplier->address) }}</textarea>
        </div>
        <div class="col-md-3">
            <label class="form-label">Payment terms *</label>
            <select name="payment_terms" class="form-select @error('payment_terms') is-invalid @enderror">
                @foreach (\App\Models\Supplier::PAYMENT_TERMS as $term)
                    <option @selected(old('payment_terms', $supplier->payment_terms) === $term)>{{ $term }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Lead time (days) *</label>
            <input type="number" min="1" max="120" name="lead_time_days" class="form-control @error('lead_time_days') is-invalid @enderror" value="{{ old('lead_time_days', $supplier->lead_time_days) }}" required>
            @error('lead_time_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Minimum order amount (₱) *</label>
            <input type="number" min="0" step="0.01" name="minimum_order_amount" class="form-control @error('minimum_order_amount') is-invalid @enderror" value="{{ old('minimum_order_amount', $supplier->minimum_order_amount ?? 0) }}" required>
            @error('minimum_order_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <div class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="is_active" @checked(old('is_active', $supplier->is_active))>
                <label class="form-check-label" for="is_active">Active supplier</label>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-primary">Save Supplier</button>
    </div>
</form>
