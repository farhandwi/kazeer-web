<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'code', 'name', 'description', 'type',
        'value', 'minimum_order_amount', 'usage_limit', 'used_count',
        'is_active', 'valid_from', 'valid_until'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_coupons')
                    ->withPivot('discount_amount')
                    ->withTimestamps();
    }

    // Check if coupon is valid
    public function isValid($orderAmount = 0)
    {
        return $this->is_active &&
               now()->between($this->valid_from, $this->valid_until) &&
               ($this->usage_limit === null || $this->used_count < $this->usage_limit) &&
               $orderAmount >= $this->minimum_order_amount;
    }

    // Calculate discount
    public function calculateDiscount($orderAmount)
    {
        if (!$this->isValid($orderAmount)) {
            return 0;
        }

        switch ($this->type) {
            case 'percentage':
                return ($orderAmount * $this->value) / 100;
            case 'fixed_amount':
                return min($this->value, $orderAmount);
            default:
                return 0;
        }
    }
}