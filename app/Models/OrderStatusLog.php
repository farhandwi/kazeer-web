<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'status', 'notes', 'changed_by_type', 'changed_by_id','created_at'
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Polymorphic relation untuk changed_by
    public function changedBy()
    {
        return $this->morphTo(__FUNCTION__, 'changed_by_type', 'changed_by_id');
    }
}