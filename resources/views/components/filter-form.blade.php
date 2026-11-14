{{-- Reusable filter form. Each field: ['name', 'label', 'type' => text|select|date, 'options' => [value => label]] --}}
@props(['action', 'fields' => []])
<form method="GET" action="{{ $action }}" class="filter-card row g-2 align-items-end">
    @foreach ($fields as $field)
        <div class="col-6 col-md">
            <label class="form-label small mb-1">{{ $field['label'] }}</label>
            @if (($field['type'] ?? 'text') === 'select')
                <select name="{{ $field['name'] }}" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($field['options'] as $value => $label)
                        <option value="{{ $value }}" @selected((string) request($field['name']) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            @else
                <input type="{{ $field['type'] ?? 'text' }}" name="{{ $field['name'] }}" value="{{ request($field['name']) }}" class="form-control form-control-sm" placeholder="{{ $field['placeholder'] ?? '' }}">
            @endif
        </div>
    @endforeach
    {{ $slot }}
    <div class="col-12 col-md-auto d-flex gap-2">
        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filter</button>
        <a href="{{ $action }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
</form>
