<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderItemStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id', 'kitchen_station_id', 'status',
        'preparation_order', 'started_at', 'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function kitchenStation(): BelongsTo
    {
        return $this->belongsTo(KitchenStation::class);
    }

    // Start preparation
    public function startPreparation()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        // Update order item status if this is the first station
        if ($this->preparation_order === 1) {
            $this->orderItem->updateItemStatus('preparing', $this->kitchen_station_id);
        }
    }

    // Complete preparation
    public function completePreparation()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Check if all stations for this item are completed
        $allStationsCompleted = $this->orderItem->stations()
                                     ->where('status', '!=', 'completed')
                                     ->count() === 0;

        if ($allStationsCompleted) {
            $this->orderItem->updateItemStatus('ready', $this->kitchen_station_id);
        }
    }
}