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