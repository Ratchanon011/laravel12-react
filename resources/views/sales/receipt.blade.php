@php
    $customer = $order->customer;
    $statusTh = ['pending' => 'รอดำเนินการ', 'shipped' => 'กำลังจัดส่ง', 'delivered' => 'จัดส่งสำเร็จ'][$order->status->value];
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ใบเสร็จ {{ $order->order_number }}</title>
    <style>
        @page { margin: 36px 40px; }
        body { font-family: 'sarabun', 'DejaVu Sans', sans-serif; font-size: 15px; color: #1f2937; }
        h1 { font-size: 28px; margin: 0; color: #111827; }
        .muted { color: #6b7280; }
        .header { width: 100%; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; margin-bottom: 18px; }
        .header td { vertical-align: top; }
        .right { text-align: right; }
        .center { text-align: center; }
        .box { width: 100%; margin-bottom: 18px; }
        .box td { vertical-align: top; width: 50%; padding-right: 12px; }
        .label { font-weight: bold; color: #4f46e5; margin-bottom: 2px; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items th { background: #eef2ff; color: #3730a3; text-align: left; padding: 7px 8px; border-bottom: 1px solid #c7d2fe; }
        table.items td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; }
        table.items th.right, table.items td.right { text-align: right; }
        table.items th.center, table.items td.center { text-align: center; }
        .total-row td { font-weight: bold; font-size: 17px; border-bottom: none; padding-top: 12px; }
        .stamp { display: inline-block; padding: 2px 12px; border: 1px solid #4f46e5; color: #4f46e5; font-weight: bold; }
        .footer { margin-top: 36px; text-align: center; color: #6b7280; font-size: 13px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <h1>ใบเสร็จรับเงิน</h1>
                <div class="muted">Receipt</div>
            </td>
            <td class="right">
                <div style="font-size: 18px; font-weight: bold;">{{ config('app.name') }}</div>
                <div class="muted">เลขที่: {{ $order->order_number }}</div>
                <div class="muted">วันที่: {{ $order->order_date->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="box">
        <tr>
            <td>
                <div class="label">ลูกค้า / Customer</div>
                <div><strong>{{ $customer->name }}</strong></div>
                @if ($customer->address)<div>{{ $customer->address }}</div>@endif
                @if ($customer->phone)<div>โทร: {{ $customer->phone }}</div>@endif
                @if ($customer->email)<div>{{ $customer->email }}</div>@endif
            </td>
            <td>
                <div class="label">สถานะออเดอร์ / Status</div>
                <div class="stamp">{{ $order->status->label() }} ({{ $statusTh }})</div>
                @if ($order->shipped_at)<div class="muted">จัดส่งเมื่อ: {{ $order->shipped_at->format('d/m/Y H:i') }}</div>@endif
                @if ($order->delivered_at)<div class="muted">ส่งมอบเมื่อ: {{ $order->delivered_at->format('d/m/Y H:i') }}</div>@endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="center" style="width: 36px;">#</th>
                <th>รายการสินค้า</th>
                <th class="right" style="width: 90px;">ราคา/หน่วย</th>
                <th class="center" style="width: 60px;">จำนวน</th>
                <th class="right" style="width: 100px;">รวม (บาท)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $i => $item)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="right">ยอดรวมสุทธิ</td>
                <td class="right">{{ number_format((float) $order->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($order->note)
        <p><span class="label">หมายเหตุ:</span> {{ $order->note }}</p>
    @endif

    <div class="footer">
        ขอบคุณที่ใช้บริการ · เอกสารนี้สร้างโดยระบบอัตโนมัติเมื่อ {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>