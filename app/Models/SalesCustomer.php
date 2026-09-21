<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesCustomer extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function orders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }
}