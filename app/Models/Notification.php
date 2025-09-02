<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'order_id', 'type', 'title', 'message',
        'data', 'target_type', 'target_id', 'is_read', 'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    // Scope untuk target tertentu
    public function scopeForTarget($query, $targetType, $targetId = null)
    {
        $query->where('target_type', $targetType);
        
        if ($targetId) {
            $query->where(function($q) use ($targetId) {
                $q->where('target_id', $targetId)->orWhereNull('target_id');
            });
        }

        return $query;
    }
}