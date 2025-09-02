<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrderQueue extends Model
{
    use HasFactory;

    protected $table = 'order_queue';

    protected $fillable = [
        'restaurant_id', 'order_id', 'queue_number', 'status',
        'estimated_wait_time', 'started_at', 'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Generate queue number
    public static function generateQueueNumber($restaurantId)
    {
        $today = now()->startOfDay();
        $lastQueue = self::where('restaurant_id', $restaurantId)
                         ->where('created_at', '>=', $today)
                         ->max('queue_number');
        
        return ($lastQueue ?? 0) + 1;
    }
}