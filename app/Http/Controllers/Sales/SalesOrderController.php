<?php

namespace App\Http\Controllers\Sales;

use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrderRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalesOrderController extends Controller
{
    /** GET /api/sales/orders?status=&search=&page= */
    public function index(Request $request): AnonymousResourceCollection
    {
        $status = SalesOrderStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('search', ''));

        $orders = SalesOrder::query()
            ->with('customer')
            ->withCount('items')
            ->when($status, fn ($q) => $q->where('status', $status->value))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('id')
            ->paginate(10);

        return SalesOrderResource::collection($orders);
    }

    /** POST /api/sales/orders */
    public function store(SalesOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $order = SalesOrder::place([
            'customer_id' => $data['customer_id'],
            'order_date' => $data['order_date'],
            'note' => $data['note'] ?? null,
            'user_id' => $request->user()?->id,
        ], $data['items']);

        return (new SalesOrderResource($this->loadAll($order)))
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/sales/orders/{order} */
    public function show(SalesOrder $order): SalesOrderResource
    {
        return new SalesOrderResource($this->loadAll($order));
    }

    /** PUT/PATCH /api/sales/orders/{order} — แก้ไขได้เฉพาะออเดอร์ที่ยังเป็น Pending */
    public function update(SalesOrderRequest $request, SalesOrder $order): SalesOrderResource|JsonResponse
    {
        if ($order->status !== SalesOrderStatus::Pending) {
            return response()->json([
                'message' => 'ออเดอร์ที่จัดส่งแล้วไม่สามารถแก้ไขได้ (แก้ไขได้เฉพาะสถานะ Pending)',
            ], 422);
        }

        $data = $request->validated();

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'],
                'note' => $data['note'] ?? null,
            ]);
            $order->syncItems($data['items']);
        });

        return new SalesOrderResource($this->loadAll($order));
    }

    /** DELETE /api/sales/orders/{order} */
    public function destroy(SalesOrder $order): JsonResponse
    {
        $order->delete(); // order_items และประวัติสถานะถูกลบตาม (cascade)

        return response()->json(['message' => 'ลบใบสั่งซื้อเรียบร้อยแล้ว']);
    }

    /** PATCH /api/sales/orders/{order}/status — Pending -> Shipped -> Delivered */
    public function updateStatus(Request $request, SalesOrder $order): SalesOrderResource|JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(SalesOrderStatus::class)],
        ]);

        $target = SalesOrderStatus::from($validated['status']);

        if (! $order->status->canTransitionTo($target)) {
            return response()->json([
                'message' => "ไม่สามารถเปลี่ยนสถานะจาก {$order->status->label()} เป็น {$target->label()} ได้",
            ], 422);
        }

        $order->moveTo($target);

        return new SalesOrderResource($this->loadAll($order));
    }

    private function loadAll(SalesOrder $order): SalesOrder
    {
        return $order->load(['customer', 'items', 'histories']);
    }
}