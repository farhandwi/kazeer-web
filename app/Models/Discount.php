<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'minimum_order',
        'maximum_discount',
        'starts_at',
        'expires_at',
        'usage_limit',
        'usage_limit_per_customer',
        'used_count',
        'applicable_restaurants',
        'applicable_categories',
        'applicable_menu_items',
        'is_active',
        'customer_eligibility',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'applicable_restaurants' => 'array',
        'applicable_categories' => 'array',
        'applicable_menu_items' => 'array',
    ];

    public function discountUsages(): HasMany
    {
        return $this->hasMany(DiscountUsage::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>=', now());
    }

    public function scopeAvailable($query)
    {
        return $query->active()
                    ->where(function ($q) {
                        $q->whereNull('usage_limit')
                          ->orWhereColumn('used_count', '<', 'usage_limit');
                    });
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->is_active 
            && $this->starts_at <= now() 
            && $this->expires_at >= now();
    }

    public function isAvailable(): bool
    {
        return $this->isActive() && (
            is_null($this->usage_limit) || 
            $this->used_count < $this->usage_limit
        );
    }

    public function canBeUsedBy($customerId): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if ($this->usage_limit_per_customer) {
            $customerUsage = $this->discountUsages()
                ->where('customer_id', $customerId)
                ->count();
            
            return $customerUsage < $this->usage_limit_per_customer;
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'percentage') {
            $discount = $amount * ($this->value / 100);
        } else {
            $discount = $this->value;
        }

        // Apply maximum discount limit
        if ($this->maximum_discount && $discount > $this->maximum_discount) {
            $discount = $this->maximum_discount;
        }

        return round($discount, 2);
    }

    public function isApplicableToRestaurant($restaurantId): bool
    {
        if (empty($this->applicable_restaurants)) {
            return true;
        }

        return in_array($restaurantId, $this->applicable_restaurants);
    }

    public function isApplicableToCategory($categoryId): bool
    {
        if (empty($this->applicable_categories)) {
            return true;
        }

        return in_array($categoryId, $this->applicable_categories);
    }

    public function isApplicableToMenuItem($menuItemId): bool
    {
        if (empty($this->applicable_menu_items)) {
            return true;
        }

        return in_array($menuItemId, $this->applicable_menu_items);
    }

    public function getFormattedValueAttribute(): string
    {
        if ($this->type === 'percentage') {
            return $this->value . '%';
        }

        return 'Rp ' . number_format($this->value, 0, ',', '.');
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->starts_at > now()) {
            return 'scheduled';
        }

        if ($this->expires_at < now()) {
            return 'expired';
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'limit_reached';
        }

        return 'active';
    }
}