<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'type', 'transaction_type', 'quantity', 'reference_type',
        'reference_id', 'reference_document', 'quantity_before', 'quantity_after', 'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** Polymorphic source of the movement (e.g. a PurchaseOrder). */
    public function reference()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStockIn(Builder $query): Builder
    {
        return $query->where('type', 'in');
    }

    public function scopeStockOut(Builder $query): Builder
    {
        return $query->where('type', 'out');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('created_at');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
    }
}
