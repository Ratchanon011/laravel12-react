<?php

namespace App\Models;

use App\Enums\SalesOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SalesOrder extends Model
{
    protected $fillable = [
        'order_number', 'customer_id', 'user_id', 'status', 'order_date',
        'note', 'total', 'shipped_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SalesOrderStatus::class,
            'order_date' => 'date',
            'total' => 'decimal:2',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(SalesCustomer::class, 'customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class, 'order_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SalesOrderStatusHistory::class, 'order_id')->orderBy('id');
    }

    /** สร้างเลขที่ออเดอร์ เช่น ORD-20260920-0001 */
    public static function generateNumber(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';

        $last = static::where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * สร้างออเดอร์ใหม่พร้อมรายการสินค้า และบันทึกประวัติสถานะเริ่มต้น (Pending)
     *
     * @param  array<string, mixed>  $attrs  customer_id, order_date, note, user_id
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function place(array $attrs, array $items): self
    {
        return DB::transaction(function () use ($attrs, $items) {
            $order = static::create($attrs + [
                'order_number' => static::generateNumber(),
                'status' => SalesOrderStatus::Pending,
                'total' => 0,
            ]);

            $order->syncItems($items);
            $order->histories()->create(['status' => SalesOrderStatus::Pending]);

            return $order;
        });
    }

    /** แทนที่รายการสินค้าทั้งหมด แล้วคำนวณยอดรวมใหม่ */
    public function syncItems(array $items): void
    {
        $this->items()->delete();

        $total = 0;
        foreach ($items as $item) {
            $qty = (int) $item['quantity'];
            $price = round((float) $item['unit_price'], 2);

            $this->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['product_name'],
                'unit_price' => $price,
                'quantity' => $qty,
            ]);

            $total += $qty * $price;
        }

        $this->update(['total' => round($total, 2)]);
    }

    /** เปลี่ยนสถานะ + บันทึกเวลาและประวัติ */
    public function moveTo(SalesOrderStatus $status): void
    {
        $this->status = $status;

        if ($status === SalesOrderStatus::Shipped) {
            $this->shipped_at = now();
        }
        if ($status === SalesOrderStatus::Delivered) {
            $this->delivered_at = now();
        }

        $this->save();
        $this->histories()->create(['status' => $status]);
    }
}