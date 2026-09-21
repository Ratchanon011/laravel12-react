<?php

namespace App\Enums;

enum SalesOrderStatus: string
{
    case Pending = 'pending';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
        };
    }

    /** สถานะถัดไปที่อนุญาตให้เปลี่ยนได้ (Pending -> Shipped -> Delivered) */
    public function next(): ?self
    {
        return match ($this) {
            self::Pending => self::Shipped,
            self::Shipped => self::Delivered,
            self::Delivered => null,
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return $this->next() === $target;
    }

    /** @return string[] */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}