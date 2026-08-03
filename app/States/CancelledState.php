<?php

namespace App\States;

class CancelledState extends OrderState
{
    public function key(): string { return 'cancelled'; }
    public function label(): string { return 'ยกเลิกคำสั่งซื้อแล้ว'; }
    public function color(): string { return 'red'; }
    public function canCancel(): bool { return false; }

    public function toArray(): array
    {
        return [
            'status' => $this->key(),
            'label' => $this->label(),
            'color' => $this->color(),
            'can_cancel' => $this->canCancel(),
            'cancelled_at' => optional($this->order->cancelled_at)->toIso8601String(),
        ];
    }
}