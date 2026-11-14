<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'sent', 'partially_received', 'received', 'cancelled'];

    public const ACTIVE_STATUSES = ['draft', 'pending', 'approved', 'sent', 'partially_received'];

    public const TAX_RATE = 0.12;

    protected $fillable = [
        'reference_number', 'supplier_id', 'created_by', 'approved_by', 'status',
        'order_date', 'expected_delivery_date', 'approved_at', 'sent_at', 'received_at',
        'rejection_reason', 'subtotal', 'tax', 'total', 'notes', 'terms', 'is_auto_generated',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'is_auto_generated' => 'boolean',
    ];

    public function supplier()
    {
        // Soft-deleted suppliers stay visible on historical purchase orders.
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function histories()
    {
        return $this->hasMany(PurchaseOrderHistory::class)->latest('id');
    }

    public function inventoryTransactions()
    {
        return $this->morphMany(InventoryTransaction::class, 'reference');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status, fn ($q) => $q->where('status', $status));
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(function ($q) use ($term) {
                $q->where('reference_number', 'like', "%{$term}%")
                    ->orWhereHas('supplier', fn ($s) => $s->withTrashed()->where('name', 'like', "%{$term}%"));
            }))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->byStatus($status))
            ->when($filters['supplier_id'] ?? null, fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('order_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('order_date', '<=', $date));
    }

    public function calculateTotal(): self
    {
        $this->loadMissing('items');
        $this->subtotal = $this->items->sum(fn ($item) => $item->line_total);
        $this->tax = round($this->subtotal * self::TAX_RATE, 2);
        $this->total = $this->subtotal + $this->tax;
        $this->save();

        return $this;
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'draft' && $this->items()->exists();
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeSent(): bool
    {
        return $this->status === 'approved';
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, ['sent', 'partially_received'], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true);
    }

    public static function generateReferenceNumber(): string
    {
        do {
            $reference = 'PO-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while (static::withTrashed()->where('reference_number', $reference)->exists());

        return $reference;
    }

    public function getStatusLabelAttribute(): string
    {
        return Str::headline($this->status);
    }
}
