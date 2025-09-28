<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'restaurant_id', 'table_id', 'customer_id',
        'customer_name', 'customer_phone', 'status', 'subtotal',
        'tax_amount', 'service_charge', 'discount_amount', 'total_amount',
        'payment_status', 'payment_method', 'special_instructions',
        'estimated_prep_time', 'confirmed_at', 'ready_at', 'served_at', 'completed_at', 'discount_id', 'discount_code', 'discount_details'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'timestamp',
        'ready_at' => 'timestamp',
        'served_at' => 'timestamp',
        'completed_at' => 'timestamp',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function queue(): HasMany
    {
        return $this->hasMany(OrderQueue::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(OrderTimeline::class)->orderBy('created_at');
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'order_coupons')
                    ->withPivot('discount_amount')
                    ->withTimestamps();
    }

    public function review(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Generate order number
    public static function generateOrderNumber($restaurantId)
    {
        $date = now()->format('Ymd');
        $lastOrder = self::where('restaurant_id', $restaurantId)
                         ->where('order_number', 'like', $restaurantId . $date . '%')
                         ->latest('id')
                         ->first();
        
        $sequence = $lastOrder ? intval(substr($lastOrder->order_number, -4)) + 1 : 1;
        return $restaurantId . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Update status dengan logging
    public function updateStatus($newStatus, $changedBy = null, $notes = null)
    {
        $oldStatus = $this->status;
        $this->update(['status' => $newStatus]);

        // Log status change
        $this->statusLogs()->create([
            'status' => $newStatus,
            'notes' => $notes,
            'changed_by_type' => $changedBy ? get_class($changedBy) : 'system',
            'changed_by_id' => $changedBy?->id,
            'created_at' => now(),
        ]);

        // Add to timeline
        $this->timeline()->create([
            'event_type' => 'status_changed',
            'title' => "Order {$newStatus}",
            'description' => "Status changed from {$oldStatus} to {$newStatus}",
            'metadata' => [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $changedBy?->name ?? 'System'
            ],
            'created_at' => now(),
        ]);

        return $this;
    }

    // Accessors & Mutators
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'served' => 'Served',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            default => ucfirst($this->payment_status),
        };
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->orderItems->sum('quantity');
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }
    
}