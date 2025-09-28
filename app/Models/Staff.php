<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;
use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable implements FilamentUser, HasName
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

    // Filament Panel Access Control
    public function canAccessPanel(Panel $panel): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin'   => in_array($this->role, ['admin', 'manager']),
            'cashier' => $this->role === 'cashier',
            'kitchen' => $this->role === 'kitchen',
            'waiter'  => $this->role === 'waiter',
            default   => false,
        };
    }

    public function canAccessFilament(): bool
    {
        return $this->is_active;
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    // Redirect path berdasarkan role
    public function redirectPath(): string
    {
        return match ($this->role) {
            'admin', 'manager' => '/admin',
            'cashier' => '/cashier',
            'kitchen' => '/kitchen',
            'waiter' => '/waiter',
            default => '/login',
        };
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function isKitchen(): bool
    {
        return $this->role === 'kitchen';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isWaiter(): bool
    {
        return $this->role === 'waiter';
    }

    // Role display name
    public function getRoleDisplayName(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'cashier' => 'Kasir',
            'kitchen' => 'Dapur',
            'waiter' => 'Pelayan',
            default => 'Unknown',
        };
    }

    // Get dashboard URL
    public function getDashboardUrl(): string
    {
        return $this->redirectPath() . '/dashboard';
    }
}