<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'product_name', 'unit_price', 'quantity'];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function getLineTotalAttribute(): float
    {
        return round((float) $this->unit_price * $this->quantity, 2);
    }
}