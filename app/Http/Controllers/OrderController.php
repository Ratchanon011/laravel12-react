<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderCannotBeCancelledException;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::latest()->get()->map(fn (Order $order) => [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'amount' => $order->amount,
            ...$order->state()->toArray(),
        ]);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): Response
    {
        $order->state()->checkTimeCondition();
        $order->refresh();

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'amount' => $order->amount,
                ...$order->state()->toArray(),
            ],
        ]);
    }

    public function cancel(Order $order): RedirectResponse
    {
        try {
            $order->state()->cancel();
        } catch (OrderCannotBeCancelledException $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        }

        return redirect()->route('orders.show', $order)->with('success', 'ยกเลิกคำสั่งซื้อเรียบร้อยแล้ว');
    }

    public function simulatePaymentSuccess(Order $order): RedirectResponse
    {
        if ($order->status === 'pending_payment') {
            $order->update(['status' => 'processing', 'paid_at' => now()]);
        }

        return redirect()->route('orders.show', $order);
    }
}