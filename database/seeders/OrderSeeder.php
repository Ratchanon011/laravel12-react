<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::create([
            'customer_name' => 'คุณสมชาย ใจดี',
            'amount' => 1250.00,
            'status' => 'pending_payment',
            'items_snapshot' => [
                ['name' => 'เสื้อยืดโอเวอร์ไซส์', 'qty' => 2, 'price' => 450],
                ['name' => 'กางเกงขาสั้น', 'qty' => 1, 'price' => 350],
            ],
        ]);
    }
}