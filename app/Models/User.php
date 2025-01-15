<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    
    /**
     * User account statuses.
     * 
     * These constants define the possible states of a user account.
     * 
     * @var string
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const SYSTEM_ADMIN_ROLE = 'admin';
    /**
     * @method bool hasRole(string|array $roles, string $guard = null)
     * @method bool can(string $permission, string $guard = null)
     */
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
     * Checks if the user has the system administrator role.
     * 
     * @return bool True if the user has the system admin role, otherwise false.
     */
    public function isSystemAdmin(): bool
    {
        return $this->hasRole(self::SYSTEM_ADMIN_ROLE);
    }

    /**
     * Automatically assigns it a customer role after registering new users
     * 
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            // Get default status
            $user->status = User::STATUS_ACTIVE;

            // Assign customer role for non-admin users
            if (!$user->hasAnyRole()) {
                $user->assignRole('customer'); // Default to customer roles
            }
        });
    }
}
