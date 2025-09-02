<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KitchenStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'name', 'description', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_stations')
                    ->withPivot('preparation_order')
                    ->orderBy('preparation_order');
    }

    public function orderItemStations(): HasMany
    {
        return $this->hasMany(OrderItemStation::class);
    }

    // Get current pending items for this station
    public function getPendingItems()
    {
        return $this->orderItemStations()
                    ->with(['orderItem.menuItem', 'orderItem.order'])
                    ->where('status', 'pending')
                    ->orderBy('preparation_order')
                    ->orderBy('created_at')
                    ->get();
    }
}