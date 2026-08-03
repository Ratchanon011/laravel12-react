<?php

namespace App\Models;

use App\States\CancelledState;
use App\States\OrderState;
use App\States\PendingPaymentState;
use App\States\ProcessingState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name', 'amount', 'status', 'items_snapshot', 'paid_at', 'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'items_snapshot' => 'array',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected const STATE_MAP = [
        'pending_payment' => PendingPaymentState::class,
        'processing' => ProcessingState::class,
        'cancelled' => CancelledState::class,
    ];

    public function state(): OrderState
    {
        $class = self::STATE_MAP[$this->status] ?? PendingPaymentState::class;
        return new $class($this);
    }

    public function minutesSinceCreated(): float
    {
        return $this->created_at->diffInSeconds(now()) / 60;
    }
}