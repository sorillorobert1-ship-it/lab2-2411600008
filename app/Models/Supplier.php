<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    public const PAYMENT_TERMS = ['COD', 'Net 15', 'Net 30', 'Net 45', 'Net 60'];

    protected $fillable = [
        'code', 'name', 'contact_person', 'email', 'phone', 'address',
        'payment_terms', 'lead_time_days', 'minimum_order_amount',
        'reliability_score', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lead_time_days' => 'integer',
        'minimum_order_amount' => 'decimal:2',
        'reliability_score' => 'decimal:2',
    ];

    /** Products whose primary supplier is this supplier. */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /** Many-to-many supplier catalogue with agreed prices and minimum order quantities. */
    public function catalogProducts()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('supplier_sku', 'unit_cost', 'minimum_order_quantity', 'is_preferred')
            ->withTimestamps();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeWithLeadTime(Builder $query, int $days): Builder
    {
        return $query->where('lead_time_days', '<=', $days);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('contact_person', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%");
        }));
    }

    public function hasPendingPurchaseOrders(): bool
    {
        return $this->purchaseOrders()->active()->exists();
    }

    public function getFullContactAttribute(): string
    {
        return collect([$this->contact_person, $this->email, $this->phone])->filter()->implode(' | ') ?: 'No contact details';
    }

    /** On-time delivery percentage of received purchase orders. */
    public function getPerformanceRatingAttribute(): float
    {
        $received = $this->purchaseOrders()->whereIn('status', ['received', 'partially_received'])->whereNotNull('received_at')->get();

        if ($received->isEmpty()) {
            return 0.0;
        }

        $onTime = $received->filter(fn ($po) => $po->expected_delivery_date && $po->received_at->startOfDay()->lte($po->expected_delivery_date))->count();

        return round($onTime / $received->count() * 100, 1);
    }
}
