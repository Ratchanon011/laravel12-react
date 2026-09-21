<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesCustomerController extends Controller
{
    /** GET /api/sales/customers */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => SalesCustomer::orderBy('name')->get(['id', 'name', 'email', 'phone', 'address']),
        ]);
    }

    /** POST /api/sales/customers */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = SalesCustomer::create($data);

        return response()->json(['data' => $customer], 201);
    }
}