<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemOption extends Model
{
    protected $fillable = [
        'order_item_id', 'menu_option_id', 'option_price'
    ];

    protected $casts = [
        'option_price' => 'decimal:2'
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function menuOption()
    {
        return $this->belongsTo(MenuOption::class);
    }
}