<?php

namespace App\States;

use App\Exceptions\OrderCannotBeCancelledException;
use App\Models\Order;

abstract class OrderState
{
    public const TIMEOUT_MINUTES = 10;

    public function __construct(protected Order $order) {}

    abstract public function key(): string;
    abstract public function label(): string;
    abstract public function color(): string;
    abstract public function canCancel(): bool;

    public function cancel(): void
    {
        throw new OrderCannotBeCancelledException(
            "ไม่สามารถยกเลิกคำสั่งซื้อได้ในสถานะ '{$this->label()}'"
        );
    }

    public function checkTimeCondition(): void {}

    abstract public function toArray(): array;
}