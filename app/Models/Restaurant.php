<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// 1. Restaurant Model
class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'phone', 'email', 'address', 'logo',
        'is_active', 'tax_rate', 'service_charge_rate', 'opening_time', 'closing_time'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tax_rate' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
    ];

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function kitchenStations(): HasMany
    {
        return $this->hasMany(KitchenStation::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(RestaurantSetting::class);
    }

    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}