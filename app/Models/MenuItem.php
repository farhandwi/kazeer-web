<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_percentage',
        'discounted_price',
        'discount_starts_at',
        'discount_ends_at',
        'is_on_discount',
        'image',
        'is_available',
        'is_featured',
        'preparation_time',
        'sort_order',
        'allergens',
        'has_options',
        'default_options',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'discount_starts_at' => 'datetime',
        'discount_ends_at' => 'datetime',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'is_on_discount' => 'boolean',
        'allergens' => 'array',
        'has_options' => 'boolean',
        'default_options' => 'array',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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

    public function optionCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            MenuOptionCategory::class,
            'menu_item_option_categories',
            'menu_item_id',
            'menu_option_category_id'
        )
        ->withPivot(['is_required', 'sort_order'])
        ->withTimestamps()
        ->orderBy('menu_item_option_categories.sort_order');
    }

    public function menuOptionCategories(): BelongsToMany
    {
        return $this->belongsToMany(MenuOptionCategory::class, 'menu_item_option_categories')
            ->withPivot(['is_required', 'sort_order'])
            ->withTimestamps()
            ->orderBy('menu_item_option_categories.sort_order', 'asc');
    }

    // Fixed: Hapus method yang duplikat dan gunakan HasMany yang benar
    public function menuItemOptionCategories(): HasMany
    {
        return $this->hasMany(MenuItemOptionCategory::class, 'menu_item_id');
    }

    public function getAvailableOptionsAttribute()
    {
        return $this->optionCategories()->with(['options' => function($query) {
            $query->where('is_available', true)->orderBy('sort_order');
        }])->get();
    }

    public function getAllergensListAttribute(): string
    {
        if (!$this->allergens || empty($this->allergens)) {
            return 'No allergens';
        }

        $allergenLabels = [
            'gluten' => 'Gluten',
            'dairy' => 'Dairy',
            'eggs' => 'Eggs',
            'fish' => 'Fish',
            'shellfish' => 'Shellfish',
            'tree_nuts' => 'Tree Nuts',
            'peanuts' => 'Peanuts',
            'soy' => 'Soy',
            'sesame' => 'Sesame',
        ];

        return collect($this->allergens)
            ->map(fn($allergen) => $allergenLabels[$allergen] ?? ucfirst($allergen))
            ->join(', ');
    }

    // Scopes
    public function scopeBySpiceLevel($query, $level)
    {
        return $query->where('spice_level', $level);
    }

    public function scopeWithOptions($query)
    {
        return $query->where('has_options', true);
    }

    // Discount Methods
    public function applyDiscount(float $percentage, ?Carbon $startsAt = null, ?Carbon $endsAt = null): void
    {
        $this->update([
            'discount_percentage' => $percentage,
            'discounted_price' => $this->price - ($this->price * $percentage / 100),
            'discount_starts_at' => $startsAt ?? now(),
            'discount_ends_at' => $endsAt,
            'is_on_discount' => true,
        ]);
    }

    public function removeDiscount(): void
    {
        $this->update([
            'discount_percentage' => null,
            'discounted_price' => null,
            'discount_starts_at' => null,
            'discount_ends_at' => null,
            'is_on_discount' => false,
        ]);
    }

    public function isDiscountActive(): bool
    {
        if (!$this->is_on_discount || !$this->discount_percentage) {
            return false;
        }

        $now = now();
        
        // Check start date
        if ($this->discount_starts_at && $this->discount_starts_at > $now) {
            return false;
        }

        // Check end date
        if ($this->discount_ends_at && $this->discount_ends_at < $now) {
            return false;
        }

        return true;
    }

    public function getCurrentPrice(): float
    {
        return $this->isDiscountActive() ? $this->discounted_price : $this->price;
    }

    public function getSavingsAmount(): float
    {
        if (!$this->isDiscountActive()) {
            return 0;
        }

        return $this->price - $this->discounted_price;
    }

    public function getSavingsPercentage(): float
    {
        if (!$this->isDiscountActive()) {
            return 0;
        }

        return round(($this->getSavingsAmount() / $this->price) * 100, 2);
    }

    // Scopes
    public function scopeOnDiscount($query)
    {
        return $query->where('is_on_discount', true)
                    ->where(function ($q) {
                        $q->whereNull('discount_starts_at')
                          ->orWhere('discount_starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('discount_ends_at')
                          ->orWhere('discount_ends_at', '>=', now());
                    });
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Accessors
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedCurrentPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->getCurrentPrice(), 0, ',', '.');
    }

    public function getDiscountStatusAttribute(): string
    {
        if (!$this->is_on_discount) {
            return 'no_discount';
        }

        if (!$this->isDiscountActive()) {
            $now = now();
            
            if ($this->discount_starts_at && $this->discount_starts_at > $now) {
                return 'scheduled';
            }
            
            if ($this->discount_ends_at && $this->discount_ends_at < $now) {
                return 'expired';
            }
        }

        return 'active';
    }

    // Boot method untuk auto-update discount status
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($menuItem) {
            // Auto calculate discounted price
            if ($menuItem->discount_percentage && $menuItem->price) {
                $menuItem->discounted_price = $menuItem->price - ($menuItem->price * $menuItem->discount_percentage / 100);
            }

            // Auto set is_on_discount based on discount_percentage
            $menuItem->is_on_discount = !is_null($menuItem->discount_percentage) && $menuItem->discount_percentage > 0;
        });
    }
}