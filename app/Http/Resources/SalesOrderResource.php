<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\SalesOrder */
class SalesOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'order_date' => $this->order_date?->format('Y-m-d'),
            'note' => $this->note,
            'total' => (float) $this->total,
            'shipped_at' => $this->shipped_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'items_count' => $this->whenCounted('items'),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
                'address' => $this->customer->address,
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'unit_price' => (float) $item->unit_price,
                'quantity' => $item->quantity,
                'line_total' => $item->line_total,
            ])->values()),
            'histories' => $this->whenLoaded('histories', fn () => $this->histories->map(fn ($h) => [
                'status' => $h->status->value,
                'status_label' => $h->status->label(),
                'at' => $h->created_at?->toIso8601String(),
            ])->values()),
        ];
    }
}