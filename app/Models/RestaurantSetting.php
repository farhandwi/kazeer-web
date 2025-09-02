<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RestaurantSetting extends Model
{
    use HasFactory;

    protected $fillable = ['restaurant_id', 'key', 'value'];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}