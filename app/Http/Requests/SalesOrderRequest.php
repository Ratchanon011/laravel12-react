<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:sales_customers,id'],
            'order_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:sales_products,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id' => 'ลูกค้า',
            'order_date' => 'วันที่สั่งซื้อ',
            'items' => 'รายการสินค้า',
            'items.*.product_name' => 'ชื่อสินค้า',
            'items.*.unit_price' => 'ราคาต่อหน่วย',
            'items.*.quantity' => 'จำนวน',
        ];
    }
}