<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SalesReceiptController extends Controller
{
    /**
     * GET /sales/orders/{order}/receipt            -> เปิดดู PDF ในเบราว์เซอร์
     * GET /sales/orders/{order}/receipt?download=1 -> ดาวน์โหลดไฟล์ PDF
     *
     * ใบเสร็จถูกสร้างจากข้อมูลออเดอร์ในฐานข้อมูลโดยอัตโนมัติทุกครั้งที่เรียก
     */
    public function show(Request $request, SalesOrder $order): Response
    {
        $order->load(['customer', 'items']);

        $pdf = Pdf::loadView('sales.receipt', ['order' => $order])
            ->setPaper('a4')
            ->setOption(['defaultFont' => 'sarabun', 'isRemoteEnabled' => false]);

        $this->registerThaiFont($pdf);

        $filename = 'receipt-'.$order->order_number.'.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    /**
     * ลงทะเบียนฟอนต์ไทย Sarabun ให้ dompdf (ทำครั้งเดียว แล้วจำไว้ใน storage/fonts)
     * ต้องมีไฟล์ resources/fonts/Sarabun-Regular.ttf และ Sarabun-Bold.ttf
     */
    private function registerThaiFont($pdf): void
    {
        $metrics = $pdf->getDomPDF()->getFontMetrics();

        $variants = [
            'normal' => 'Sarabun-Regular.ttf',
            'bold' => 'Sarabun-Bold.ttf',
        ];

        // ถ้าเคยลงทะเบียนครบแล้ว ไม่ต้องทำซ้ำ
        $registered = $metrics->getFontFamilies()['sarabun'] ?? [];
        if (isset($registered['normal'], $registered['bold'])) {
            return;
        }

        if (! is_dir(storage_path('fonts'))) {
            mkdir(storage_path('fonts'), 0775, true);
        }

        foreach ($variants as $weight => $file) {
            $path = resource_path('fonts/'.$file);

            if (! is_file($path)) {
                throw new \RuntimeException("ไม่พบไฟล์ฟอนต์: {$path}");
            }

            $ok = $metrics->registerFont(
                ['family' => 'sarabun', 'style' => 'normal', 'weight' => $weight],
                str_replace('\\', '/', $path),
            );

            if (! $ok) {
                throw new \RuntimeException("ลงทะเบียนฟอนต์ไม่สำเร็จ: {$path} (ตรวจว่า storage/fonts เขียนได้ และไฟล์ .ttf ไม่เสีย)");
            }
        }
    }
}