<?php

namespace App\Http\Controllers;


abstract class Controller
{
    //
}
// app/Http/Controllers/OrderTrackingController.php

use App\Models\Order;
use Inertia\Inertia;

class OrderTrackingController extends Controller {
    public function show(Order $order) {
        // 1. เรียกใช้เงื่อนไขเวลาก่อน (ถ้าหมดเวลา สถานะจะเปลี่ยนใน DB ทันที)
        $order->getState()->checkTimeCondition($order);
        
        // 2. โหลด State ล่าสุดหลังจากเช็กเงื่อนไขเวลาแล้ว
        $currentState = $order->refresh()->getState();

        // 3. ส่ง Data ไปที่ React ผ่าน Inertia
        return Inertia::render('Order/Tracking', [
            'order' => [
                'id' => $order->id,
                'status' => $currentState->getStatusSlug(),
                'status_label' => $currentState->getStatusLabel(),
                'can_cancel' => $currentState->canCancel($order),
                'created_at' => $order->created_at->toIso8601String(),
                // ส่งเวลาหมดอายุไปให้ Frontend ทำหน้าจอนับถอยหลัง (บวก 10 นาที)
                'expires_at' => $order->created_at->addMinutes(10)->toIso8601String(),
            ]
        ]);
    }
}

