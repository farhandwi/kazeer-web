<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MenuOptionCategory extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'type', 'is_required', 'sort_order'
    ];

    public function options()
    {
        return $this->hasMany(MenuOption::class, 'option_category_id')->orderBy('sort_order');
    }

    // Boot method for auto-generating slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function menuOptions(): HasMany
    {
        return $this->hasMany(MenuOption::class, 'option_category_id')->orderBy('sort_order');
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(
            MenuItem::class,
            'menu_item_option_categories', // pivot table
            'menu_option_category_id',          // FK ke menu_option_categories
            'menu_item_id'                 // FK ke menu_items
        )
        ->withPivot(['is_required', 'sort_order'])
        ->withTimestamps()
        ->orderBy('menu_item_option_categories.sort_order');
    }
    
    
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'single' => 'Single Selection',
            'multiple' => 'Multiple Selection',
            default => ucfirst($this->type),
        };
    }

    // Scopes
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    public function menuItemOptionCategories()
    {
        return $this->hasMany(MenuItemOptionCategory::class, 'option_category_id');
    }
}
