<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountUsage extends Model
{
    protected $fillable = [
        'discount_id', 'order_id', 'customer_id', 'discount_amount'
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2'
    ];

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}