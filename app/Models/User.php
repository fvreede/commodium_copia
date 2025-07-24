<?php

/**
 * Bestandsnaam: User.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.14
 * Datum: 2025-07-24
 * Tijd: 21:16:00
 * Doel: Eloquent User Model voor het authenticatie systeem. Beheert gebruikersaccounts met rol-gebaseerde toegang, adressen, bestellingen en account status. Gebruikt Spatie permissions voor uitgebreide role & permission management.
 */

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Log;

/**
 * User Model
 *
 * Representeert een gebruiker in het systeem met authenticatie en rol mogelijkheden.
 *
 * @property int $id De unieke identifier
 * @property string $name De volledige naam van de gebruiker
 * @property string $email Het email adres van de gebruiker
 * @property \Illuminate\Support\Carbon|null $email_verified_at Wanneer het email geverifieerd werd
 * @property string $password Het gehashte wachtwoord
 * @property string $status De account status van de gebruiker (active/suspended)
 * @property string|null $remember_token Het "onthoud mij" token
 * @property \Illuminate\Support\Carbon $created_at Wanneer de gebruiker aangemaakt werd
 * @property \Illuminate\Support\Carbon $updated_at Wanneer de gebruiker laatst bijgewerkt werd
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \App\Models\UserAddress|null $address
 * @property-read \App\Models\UserAddress|null $userAddress
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $cartItems
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static User|null find($id, $columns = ['*'])
 * @method static User findOrFail($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|User[] all($columns = ['*'])
 * @method bool hasRole(string|array $roles, string $guard = null)
 * @method bool hasAnyRole(string|array $roles)
 * @method void assignRole(string|array $roles)
 * @method bool can(string $permission, string $guard = null)
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Order> orders()
 * @method \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\UserAddress> address()
 * @method \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\UserAddress> userAddress()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\CartItem> cartItems()
 *
 * @uses \Illuminate\Database\Eloquent\Factories\HasFactory
 * @uses \Illuminate\Notifications\Notifiable
 * @uses \Spatie\Permission\Traits\HasRoles
 */
class User extends Authenticatable
{
    /**
     * Gebruikersaccount statussen
     * 
     * Deze constanten definiëren de mogelijke statussen van een gebruikersaccount
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    
    /**
     * Systeem rollen constanten
     * 
     * Definiëren de belangrijkste rol types in het systeem
     */
    const SYSTEM_ADMIN_ROLE = 'admin';
    const EDITOR_ROLE = 'editor';

    /**
     * Gebruik Laravel traits voor uitgebreide functionaliteit
     * 
     * - HasFactory: Voor model factories (testing/seeding)
     * - Notifiable: Voor Laravel notifications
     * - HasRoles: Voor Spatie permission rol management
     */
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'users';

    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',     // Volledige naam van de gebruiker
        'email',    // Email adres (ook gebruikt voor login)
        'password', // Wachtwoord (wordt automatisch gehasht)
        'status',   // Account status (active/suspended)
    ];

    /**
     * Attributen die verborgen moeten worden bij serialisatie
     * Deze velden worden nooit getoond in JSON responses voor beveiliging
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',      // Wachtwoord nooit tonen
        'remember_token', // Remember token nooit tonen
    ];

    /**
     * Krijg de attributen die gecast moeten worden naar specifieke types
     * Zorgt voor automatische conversie tussen database en PHP types
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Email verificatie als Carbon datetime
            'password' => 'hashed',            // Automatisch hashen van wachtwoorden
        ];
    }

    /**
     * ROL CONTROLE METHODES
     */

    /**
     * Controleer of de gebruiker systeembeheerder rechten heeft
     * 
     * @return bool True als gebruiker admin rol heeft, anders false
     */
    public function isSystemAdmin(): bool
    {
        return $this->hasRole(self::SYSTEM_ADMIN_ROLE);
    }

    /**
     * Controleer of de gebruiker editor rechten heeft
     * 
     * @return bool True als gebruiker editor rol heeft, anders false
     */
    public function isEditor(): bool
    {
        return $this->hasRole(self::EDITOR_ROLE);
    }

    /**
     * Controleer of de gebruiker klant rechten heeft
     * 
     * @return bool True als gebruiker customer rol heeft, anders false
     */
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    /**
     * MODEL EVENTS - Automatische acties bij model wijzigingen
     */

    /**
     * Boot het model met automatische event handlers
     * Wijst automatisch customer rol toe aan nieuwe gebruikers
     * 
     * @return void
     */
    protected static function booted()
    {
        // Event handler voor wanneer nieuwe gebruiker wordt aangemaakt
        static::creating(function ($user) {
            Log::info('Creating new user', ['email' => $user->email]);
            
            // Stel standaard status in op actief
            $user->status = User::STATUS_ACTIVE;
            Log::info('Status set', ['status' => $user->status]);
        });

        // Event handler voor nadat gebruiker is opgeslagen (NA opslaan)
        static::created(function ($user) {
            Log::info('User created, assigning role', ['user_id' => $user->id, 'email' => $user->email]);
            
            // Rol is altijd klant na registreren (wordt automatisch ingesteld)
            if (!$user->hasAnyRole()) {
                try {
                    $user->assignRole('customer');
                    Log::info('Customer role assigned successfully', ['user_id' => $user->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to assign customer role', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }

    /**
     * RELATIES
     */

    /**
     * Krijg alle bestellingen van deze gebruiker
     * Een gebruiker kan meerdere bestellingen hebben (One-to-Many)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Order>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Krijg het bezorgadres dat bij deze gebruiker hoort
     * 
     * Definieert een een-op-een relatie tussen gebruiker en bezorgadres.
     * Wordt primair gebruikt om het standaard adres op te halen tijdens checkout.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\UserAddress>
     */
    public function address(): HasOne
    {
        return $this->hasOne(UserAddress::class);
    }

    /**
     * Krijg het adres van de gebruiker (alias voor address relatie)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\UserAddress>
     */
    public function userAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class);
    }

    /**
     * Krijg alle winkelwagen items van deze gebruiker
     * Een gebruiker kan meerdere cart items hebben (One-to-Many)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\CartItem>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}