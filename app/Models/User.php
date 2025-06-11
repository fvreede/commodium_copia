<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Support\Facades\Log;

/**
 * User Model
 * 
 * Represents a user in the system with authentication and role capabilities.
 * 
 * @property int $id The unique identifier
 * @property string $name The user's full name
 * @property string $email The user's email address
 * @property \DateTime|null $email_verified_at When the email was verified
 * @property string $password The hashed password
 * @property string $status The user's account status (active/suspended)
 * @property string|null $remember_token The remember me token
 * @property \DateTime $created_at When the user was created
 * @property \DateTime $updated_at When the user was last updated
 * 
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method bool hasRole(string|array $roles, string $guard = null)
 * @method bool hasAnyRole(string|array $roles)
 * @method void assignRole(string|array $roles)
 * @method bool can(string $permission, string $guard = null)
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * 
 * @uses \Illuminate\Database\Eloquent\Factories\HasFactory
 * @uses \Illuminate\Notifications\Notifiable
 * @uses \Spatie\Permission\Traits\HasRoles
 */

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
    const EDITOR_ROLE = 'editor';
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
     * Checks if the user has the editor role.
     * 
     * @return bool True if the user has the editor role, otherwise false.
     */
    public function isEditor(): bool
    {
        return $this->hasRole(self::EDITOR_ROLE);
    }

    /**
     * Checks if the user has the customer role.
     * 
     * @return bool True if the user has the customer role, otherwise false.
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * Automatically assigns it a customer role after registering new users
     * 
     * @return void
     */
    protected static function booted()
{
    static::creating(function ($user) {
        Log::info('Creating new user', ['email' => $user->email]);
        
        // Get default status
        $user->status = User::STATUS_ACTIVE;
        Log::info('Status set', ['status' => $user->status]);

        // Assign customer role for non-admin users
        if (!$user->hasAnyRole()) {
            try {
                $user->assignRole('customer');
                Log::info('Role assigned successfully');
            } catch (\Exception $e) {
                Log::error('Failed to assign role', ['error' => $e->getMessage()]);
            }
        }
    });
}

    /**
     * Get all orders for the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the delivery address associated with the user.
     * 
     * This defines a one-to-one relationship between a user and their delivery address.
     * Used primarily to retrieve the default address during checkout.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function address() 
    {
        return $this->hasOne(UserAddress::class);
    }
}
