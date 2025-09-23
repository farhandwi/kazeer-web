<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
        'last_login_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    // Event listeners
    protected static function boot()
    {
        parent::boot();

        // Ketika staff dibuat, buat juga user
        static::created(function ($staff) {
            User::updateOrCreate(
                ['email' => $staff->email],
                [
                    'name' => $staff->name,
                    'password' => $staff->password,
                    'email_verified_at' => $staff->email_verified_at,
                ]
            );
        });

        // Ketika staff diupdate, update juga user
        static::updated(function ($staff) {
            $user = User::where('email', $staff->email)->first();
            if ($user) {
                $updateData = [
                    'name' => $staff->name,
                ];

                // Hanya update password jika ada perubahan
                if ($staff->isDirty('password')) {
                    $updateData['password'] = $staff->password;
                }

                $user->update($updateData);
            }
        });

        // Ketika staff dihapus, hapus juga user (opsional)
        static::deleted(function ($staff) {
            User::where('email', $staff->email)->delete();
        });
    }
}