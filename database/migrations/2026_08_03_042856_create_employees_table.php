<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_code')->unique(); // คอลัมน์ที่ 1
            $table->string('first_name');         // คอลัมน์ที่ 2
            $table->string('last_name');          // คอลัมน์ที่ 3
            $table->string('department');         // คอลัมน์ที่ 4
            $table->decimal('salary', 10, 2);     // คอลัมน์ที่ 5
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
