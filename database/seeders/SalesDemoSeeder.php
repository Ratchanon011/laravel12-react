<?php

namespace Database\Seeders;

use App\Enums\SalesOrderStatus;
use App\Models\SalesCustomer;
use App\Models\SalesOrder;
use App\Models\SalesProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ใช้ผู้ใช้คนแรกที่มีอยู่ในระบบเป็นผู้สร้างออเดอร์ตัวอย่าง (ไม่มีก็ได้)
        $user = User::query()->first();

        $customers = collect([
            ['name' => 'บริษัท สยามเทค จำกัด', 'email' => 'contact@siamtech.example', 'phone' => '02-123-4567', 'address' => '99 ถ.สุขุมวิท กรุงเทพฯ 10110'],
            ['name' => 'คุณสมชาย ใจดี', 'email' => 'somchai@example.com', 'phone' => '081-234-5678', 'address' => '12/3 ถ.นิมมานเหมินท์ เชียงใหม่ 50200'],
            ['name' => 'ร้านมั่นคงการค้า', 'email' => null, 'phone' => '089-876-5432', 'address' => '45 ถ.มิตรภาพ ขอนแก่น 40000'],
            ['name' => 'คุณวิภา รักเรียน', 'email' => 'wipa@example.com', 'phone' => '086-111-2222', 'address' => null],
        ])->map(fn ($c) => SalesCustomer::firstOrCreate(['name' => $c['name']], $c));

        $products = collect([
            ['sku' => 'P-1001', 'name' => 'คีย์บอร์ดไร้สาย', 'price' => 590],
            ['sku' => 'P-1002', 'name' => 'เมาส์ไร้สาย', 'price' => 350],
            ['sku' => 'P-1003', 'name' => 'จอมอนิเตอร์ 24 นิ้ว', 'price' => 4290],
            ['sku' => 'P-1004', 'name' => 'หูฟังครอบหู', 'price' => 1290],
            ['sku' => 'P-1005', 'name' => 'เว็บแคม Full HD', 'price' => 890],
            ['sku' => 'P-1006', 'name' => 'แฟลชไดรฟ์ 64GB', 'price' => 250],
        ])->map(fn ($p) => SalesProduct::firstOrCreate(['sku' => $p['sku']], $p));

        if (SalesOrder::count() > 0) {
            return; // ไม่สร้างออเดอร์ตัวอย่างซ้ำ
        }

        $line = fn (int $productIndex, int $qty) => [
            'product_id' => $products[$productIndex]->id,
            'product_name' => $products[$productIndex]->name,
            'unit_price' => $products[$productIndex]->price,
            'quantity' => $qty,
        ];

        $samples = [
            [0, [[0, 2], [1, 2]], SalesOrderStatus::Delivered],
            [1, [[2, 1]], SalesOrderStatus::Shipped],
            [2, [[3, 1], [5, 4]], SalesOrderStatus::Pending],
            [3, [[4, 1], [1, 1]], SalesOrderStatus::Pending],
            [0, [[2, 3], [0, 3]], SalesOrderStatus::Shipped],
            [1, [[5, 10]], SalesOrderStatus::Delivered],
        ];

        foreach ($samples as [$customerIndex, $lines, $target]) {
            $order = SalesOrder::place([
                'customer_id' => $customers[$customerIndex]->id,
                'order_date' => now()->subDays(rand(0, 10))->toDateString(),
                'note' => null,
                'user_id' => $user?->id,
            ], array_map(fn ($l) => $line($l[0], $l[1]), $lines));

            if ($target === SalesOrderStatus::Shipped || $target === SalesOrderStatus::Delivered) {
                $order->moveTo(SalesOrderStatus::Shipped);
            }
            if ($target === SalesOrderStatus::Delivered) {
                $order->moveTo(SalesOrderStatus::Delivered);
            }
        }
    }
}