<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesProduct;
use Illuminate\Http\JsonResponse;

class SalesProductController extends Controller
{
    /** GET /api/sales/products */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => SalesProduct::orderBy('name')->get()->map(fn ($p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'price' => (float) $p->price,
            ]),
        ]);
    }
}