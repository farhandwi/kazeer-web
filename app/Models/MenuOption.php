<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MenuOption extends Model
{
    protected $fillable = [
        'option_category_id', 'name', 'slug', 'description', 
        'additional_price', 'is_available', 'sort_order'
    ];

    protected $casts = [
        'additional_price' => 'decimal:2'
    ];

    public function category()
    {
        return $this->belongsTo(MenuOptionCategory::class, 'option_category_id');
    }

    // Boot method for auto-generating slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($option) {
            if (empty($option->slug)) {
                $option->slug = Str::slug($option->name);
            }
        });
    }

    public function optionCategory(): BelongsTo
    {
        return $this->belongsTo(MenuOptionCategory::class, 'option_category_id');
    }

    public function orderItemOptions(): HasMany
    {
        return $this->hasMany(OrderItemOption::class, 'menu_option_id');
    }

    public function getFormattedAdditionalPriceAttribute(): string
    {
        if ($this->additional_price == 0) {
            return 'Free';
        }
        
        $prefix = $this->additional_price > 0 ? '+' : '';
        return $prefix . 'Rp ' . number_format($this->additional_price, 0, ',', '.');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFree($query)
    {
        return $query->where('additional_price', 0);
    }

    public function scopePaid($query)
    {
        return $query->where('additional_price', '>', 0);
    }
}
