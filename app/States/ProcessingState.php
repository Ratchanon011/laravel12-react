<?php

namespace App\States;

class ProcessingState extends OrderState
{
    public function key(): string { return 'processing'; }
    public function label(): string { return 'กำลังเตรียมจัดส่ง'; }
    public function color(): string { return 'teal'; }
    public function canCancel(): bool { return false; }

    public function toArray(): array
    {
        return [
            'status' => $this->key(),
            'label' => $this->label(),
            'color' => $this->color(),
            'can_cancel' => $this->canCancel(),
            'paid_at' => optional($this->order->paid_at)->toIso8601String(),
        ];
    }
}