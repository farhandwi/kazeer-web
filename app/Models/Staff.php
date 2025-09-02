<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'name', 'email', 'password', 'role',
        'phone', 'is_active', 'last_login_at'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}