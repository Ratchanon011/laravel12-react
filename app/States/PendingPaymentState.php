<?php

namespace App\States;

class PendingPaymentState extends OrderState
{
    public function key(): string { return 'pending_payment'; }
    public function label(): string { return 'รอชำระเงิน'; }
    public function color(): string { return 'amber'; }
    public function canCancel(): bool { return true; }

    public function cancel(): void
    {
        $this->order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
    }

    public function checkTimeCondition(): void
    {
        if ($this->order->minutesSinceCreated() > self::TIMEOUT_MINUTES) {
            $this->order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }
    }

    public function toArray(): array
    {
        $expiresAt = $this->order->created_at->addMinutes(self::TIMEOUT_MINUTES);

        return [
            'status' => $this->key(),
            'label' => $this->label(),
            'color' => $this->color(),
            'can_cancel' => $this->canCancel(),
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }
}