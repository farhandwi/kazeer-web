<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Staff;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the staff record associated with the user.
     */
    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'email', 'email');
    }

    /**
     * Check if user is a staff member
     */
    public function isStaff(): bool
    {
        return $this->staff()->exists();
    }

    /**
     * Get staff role if user is staff
     */
    public function getStaffRole(): ?string
    {
        return $this->staff?->role;
    }

    /**
     * Check if staff is active
     */
    public function isActiveStaff(): bool
    {
        return $this->staff?->is_active ?? false;
    }
}