<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemOptionCategory extends Model
{
    protected $table = 'menu_item_option_categories';
    protected $fillable = ['menu_item_id','menu_option_category_id','is_required','sort_order'];

    protected $casts = [
        'is_required' => 'boolean'
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function optionCategory(): BelongsTo
    {
        return $this->belongsTo(MenuOptionCategory::class, 'menu_option_category_id');
    }
}

