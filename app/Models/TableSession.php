<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableSession extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'table_sessions';

    protected $fillable = [
        'restaurant_id', 'table_id', 'session_token',
        'guest_count', 'status', 'started_at', 'ended_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    // Generate session token
    public static function generateSessionToken($tableId)
    {
        return 'TBL_' . $tableId . '_' . time() . '_' . rand(1000, 9999);
    }

    // End session
    public function endSession()
    {
        $this->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        // Update table status
        $this->table->update(['status' => 'available']);
    }
}
