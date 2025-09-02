<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'table_number', 'qr_code', 
        'capacity', 'status', 'description'
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TableSession::class);
    }

    public function currentSession()
    {
        return $this->sessions()->where('status', 'active')->latest()->first();
    }

    public function generateQRCode()
    {
        $this->qr_code = 'QR_' . $this->restaurant_id . '_' . $this->table_number . '_' . time();
        $this->save();
        return $this->qr_code;
    }
}
