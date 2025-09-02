<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'category_id', 'name', 'slug', 'description',
        'price', 'image', 'is_available', 'is_featured', 'preparation_time',
        'sort_order', 'allergens', 'spice_level'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'allergens' => 'array',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MenuItemVariant::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function kitchenStations(): BelongsToMany
    {
        return $this->belongsToMany(KitchenStation::class, 'menu_item_stations')
                    ->withPivot('preparation_order')
                    ->orderBy('preparation_order');
    }
}