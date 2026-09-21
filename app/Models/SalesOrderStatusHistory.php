<?php

namespace App\Models;

use App\Enums\SalesOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderStatusHistory extends Model
{
    protected $fillable = ['order_id', 'status'];

    protected function casts(): array
    {
        return ['status' => SalesOrderStatus::class];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }
}