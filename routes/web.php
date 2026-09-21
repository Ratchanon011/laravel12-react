<?php

use App\Http\Controllers\ProfileController;
use App\Models\Product;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/test', function () {
    return Inertia::render('Test');
})->name('test');

Route::get('/hello-teacher', function () {
    return Inertia::render('HelloTeacher');
})->name('hello-teacher');


Route::get('/about-page', function () {
    return Inertia::render('AboutPage');
})->name('about-page');

Route::get('/home-page', function () {
    return Inertia::render('HomePage');
})->name('home-page');

Route::get('/bootstrap', function () {
    return Inertia::render('BootstrapContent');
})->name('bootstrap');

Route::get('/circle', function () {
    return Inertia::render('Circle');
})->name('circle');

Route::get('/counter', function () {
    return Inertia::render('Counter');
})->name('counter');

Route::get('/form-example', function () {
    return Inertia::render('FormExample');
})->name('form-example');

Route::get('/list-manager', function () {
    return Inertia::render('ListManager');
})->name('list-manager');

Route::get('/infinite-scroll', function () {
    return Inertia::render('InfiniteScrollExample');
})->name('infinite-scroll');

// routes/web.php
// use App\Models\Product;
Route::get('/product', function () {
    $products = Product::all();
    return Inertia::render('ProductList', compact('products') );
})->name('product');

// routes/web.php
Route::get('/product-others', function () {
    return Inertia::render('ProductOthers');
})->name('product-others');


use App\Http\Controllers\OrderController;

Route::get('/Quiz3/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/Quiz3/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
Route::get('/Quiz3', [OrderController::class, 'index'])->name('orders.index');  
Route::post('/Quiz3/{order}/simulate-payment', [OrderController::class, 'simulatePaymentSuccess'])->name('orders.simulate-payment');

use App\Models\Employee;

Route::get('/quiz4', function () {
    return Inertia::render('Quiz4'); 
});

Route::get('/api/quiz4-data', function () {
    return response()->json(Employee::all());
});

// ... โค้ด route อื่นๆ ...

Route::get('/api/quiz4-data', function () {
    // ดึงข้อมูลจากตาราง employees โดยตรง ไม่ต้องผ่าน Model
    $employees = DB::table('employees')->get();
    return response()->json($employees);
});

Route::get('/add-data', function () {
    DB::table('employees')->insert([
        ['emp_code' => 'EMP001', 'first_name' => 'Somchai', 'last_name' => 'Jaidee', 'department' => 'IT', 'salary' => 45000],
        ['emp_code' => 'EMP002', 'first_name' => 'Somsri', 'last_name' => 'Maneerat', 'department' => 'HR', 'salary' => 35000],
        ['emp_code' => 'EMP003', 'first_name' => 'Mana', 'last_name' => 'Rakthai', 'department' => 'Marketing', 'salary' => 42000],
        ['emp_code' => 'EMP004', 'first_name' => 'Wichai', 'last_name' => 'Suksawat', 'department' => 'Sales', 'salary' => 38000],
        ['emp_code' => 'EMP005', 'first_name' => 'Naree', 'last_name' => 'Pongsapat', 'department' => 'Finance', 'salary' => 48000]
    ]);
    
    return "เพิ่มข้อมูลสำเร็จ 5 รายการ! กลับไปดูหน้าเว็บได้เลยครับ 🎉";
});

Route::get('/product-manager', function () {
    $p = Product::all();
    return Inertia::render('ProductManager', compact('p'));
})->name('product-manager');


Route::get('/product/create', function () {
    return Inertia::render('ProductForm');
})->name('product.create');

Route::get('/product/{id}/edit', function ($id) {
    $product = Product::findOrFail($id);
    return Inertia::render('ProductForm', compact('product'));
})->name('product.edit');

// ===== วางส่วน use ไว้ด้านบนของ routes/web.php และวางบล็อก Route::middleware ก่อนบรรทัด require __DIR__.'/auth.php'; =====

use App\Http\Controllers\Sales\SalesCustomerController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\Sales\SalesProductController;
use App\Http\Controllers\Sales\SalesReceiptController;
use App\Http\Controllers\Sales\SalesSummaryController;
use App\Models\SalesOrder;

Route::middleware('auth')->prefix('sales')->name('sales.')->group(function () {
    // ---- หน้าเว็บ (Inertia + React) : /sales/orders ----
    Route::get('/orders', fn () => Inertia::render('Sales/Orders/Index'))->name('orders.index');
    Route::get('/orders/create', fn () => Inertia::render('Sales/Orders/Form'))->name('orders.create');
    Route::get('/orders/{order}', fn (SalesOrder $order) => Inertia::render('Sales/Orders/Show', ['orderId' => $order->id]))
        ->whereNumber('order')->name('orders.show');
    Route::get('/orders/{order}/edit', fn (SalesOrder $order) => Inertia::render('Sales/Orders/Form', ['orderId' => $order->id]))
        ->whereNumber('order')->name('orders.edit');

    // ---- ใบเสร็จ PDF : /sales/orders/{id}/receipt ----
    Route::get('/orders/{order}/receipt', [SalesReceiptController::class, 'show'])
        ->whereNumber('order')->name('orders.receipt');
});

// ---- REST API (JSON) : /api/sales/... (ใช้ session ของ Breeze) ----
Route::middleware('auth')->prefix('api/sales')->name('api.sales.')->group(function () {
    Route::get('summary', SalesSummaryController::class)->name('summary');

    Route::get('customers', [SalesCustomerController::class, 'index'])->name('customers.index');
    Route::post('customers', [SalesCustomerController::class, 'store'])->name('customers.store');

    Route::get('products', [SalesProductController::class, 'index'])->name('products.index');

    Route::patch('orders/{order}/status', [SalesOrderController::class, 'updateStatus'])
        ->whereNumber('order')->name('orders.status');
    Route::apiResource('orders', SalesOrderController::class);
});
