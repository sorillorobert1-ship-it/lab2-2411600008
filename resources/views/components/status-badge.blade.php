@props(['status'])
@php
    $colors = [
        'draft' => 'secondary', 'pending' => 'warning text-dark', 'approved' => 'primary', 'rejected' => 'danger',
        'sent' => 'info text-dark', 'partially_received' => 'warning text-dark', 'received' => 'success', 'cancelled' => 'dark',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'badge bg-'.($colors[$status] ?? 'secondary')]) }}>{{ \Illuminate\Support\Str::headline($status) }}</span>
