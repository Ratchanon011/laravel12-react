<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('sales_customers')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->date('order_date');
            $table->text('note')->nullable();
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('sales_products')->nullOnDelete();
            $table->string('product_name');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });

        Schema::create('sales_order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_status_histories');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};