<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesProduct extends Model
{
    protected $fillable = ['sku', 'name', 'price'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }
}