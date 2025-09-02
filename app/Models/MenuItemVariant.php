<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MenuItemVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_item_id', 'name', 'value', 'price_modifier',
        'is_required', 'sort_order'
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_required' => 'boolean',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function orderItems(): BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class, 'order_item_variants')
                    ->withPivot('price_modifier')
                    ->withTimestamps();
    }
}