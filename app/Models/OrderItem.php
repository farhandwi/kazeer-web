<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'menu_item_id', 'quantity', 'unit_price',
        'total_price', 'special_instructions', 'status',
        'started_at', 'ready_at', 'served_at'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'started_at' => 'datetime',
        'ready_at' => 'datetime',
        'served_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(MenuItemVariant::class, 'order_item_variants')
                    ->withPivot('price_modifier')
                    ->withTimestamps();
    }

    public function stations(): HasMany
    {
        return $this->hasMany(OrderItemStation::class);
    }

    // Update status item dengan tracking per station
    public function updateItemStatus($newStatus, $stationId = null)
    {
        $this->update(['status' => $newStatus]);

        if ($stationId) {
            $this->stations()->where('kitchen_station_id', $stationId)
                 ->update(['status' => $newStatus]);
        }

        // Add to order timeline
        $this->order->timeline()->create([
            'event_type' => 'item_status_changed',
            'title' => "Item {$this->menuItem->name} {$newStatus}",
            'description' => "Status changed to {$newStatus}",
            'metadata' => [
                'item_id' => $this->id,
                'item_name' => $this->menuItem->name,
                'station_id' => $stationId,
                'new_status' => $newStatus
            ],
            'created_at' => now(),
        ]);
    }
}