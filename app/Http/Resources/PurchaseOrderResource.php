<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'supplier' => $this->whenLoaded('supplier', fn () => $this->supplier->name),
            'status' => $this->status,
            'status_label' => $this->status_label,
            'order_date' => $this->order_date?->toDateString(),
            'expected_delivery_date' => $this->expected_delivery_date?->toDateString(),
            'total' => (float) $this->total,
            'url' => route('purchase-orders.show', $this->id),
        ];
    }
}
