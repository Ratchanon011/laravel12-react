<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Auto-cancel pending-payment orders past the 10-minute timeout.';

    public function handle(): void
    {
        $orders = Order::where('status', 'pending_payment')->get();

        foreach ($orders as $order) {
            $order->state()->checkTimeCondition();
        }

        $this->info("Checked {$orders->count()} pending order(s).");
    }
}