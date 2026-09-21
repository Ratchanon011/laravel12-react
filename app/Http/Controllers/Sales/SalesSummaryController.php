<?php

namespace App\Http\Controllers\Sales;

use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;

class SalesSummaryController extends Controller
{
    /** GET /api/sales/summary — จำนวนออเดอร์แยกตามสถานะ และยอดขายที่ส่งมอบแล้ว */
    public function __invoke(): JsonResponse
    {
        $rows = SalesOrder::query()
            ->toBase()
            ->selectRaw('status, count(*) as c, sum(total) as t')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $out = [];
        foreach (SalesOrderStatus::cases() as $status) {
            $out[$status->value] = (int) ($rows->get($status->value)?->c ?? 0);
        }

        return response()->json($out + [
            'total' => array_sum($out),
            'revenue' => (float) ($rows->get(SalesOrderStatus::Delivered->value)?->t ?? 0),
        ]);
    }
}