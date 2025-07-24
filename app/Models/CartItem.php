<?php

/**
 * Bestandsnaam: CartItem.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-24
 * Tijd: 21:14:00
 * Doel: Eloquent Model voor winkelwagen items. Representeert individuele producten in de winkelwagen van gebruikers met hoeveelheid, prijs en automatische totaal berekening.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CartItem Model
 *
 * @property int $id De unieke identifier van het cart item
 * @property int $user_id ID van de gebruiker die eigenaar is van dit cart item
 * @property int $product_id ID van het product dat in de winkelwagen zit
 * @property int $quantity Aantal van dit product in de winkelwagen
 * @property float $price Prijs per stuk ten tijde van toevoegen aan winkelwagen
 * @property \Illuminate\Support\Carbon $created_at Aanmaakdatum
 * @property \Illuminate\Support\Carbon $updated_at Laatste wijzigingsdatum
 * @property-read \App\Models\User $user De gebruiker die eigenaar is van dit cart item
 * @property-read \App\Models\Product $product Het product dat in dit cart item zit
 * @property-read float $total Berekent automatisch het totaal bedrag (quantity × price)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereUpdatedAt($value)
 * @method static CartItem|null find($id, $columns = ['*'])
 * @method static CartItem findOrFail($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|CartItem[] all($columns = ['*'])
 */
class CartItem extends Model
{
    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'cart_items';

    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'user_id',      // ID van de gebruiker die eigenaar is van dit cart item
        'product_id',   // ID van het product dat in de winkelwagen zit
        'quantity',     // Aantal van dit product in de winkelwagen
        'price',        // Prijs per stuk ten tijde van toevoegen aan winkelwagen
    ];

    /**
     * Attribute casting voor juiste data types
     * Zorgt voor automatische conversie tussen database en PHP types
     */
    protected $casts = [
        'quantity' => 'integer',    // Aantal moet altijd een geheel getal zijn
        'price' => 'decimal:2',     // Prijs met 2 decimalen voor nauwkeurigheid
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Computed attributes die automatisch toegevoegd worden aan model output
     * Deze berekende velden zijn beschikbaar via $cartItem->total
     */
    protected $appends = ['total'];

    /**
     * Relatie naar de gebruiker die eigenaar is van dit winkelwagen item
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\CartItem>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relatie naar het product dat in dit winkelwagen item zit
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Product, \App\Models\CartItem>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Berekent automatisch het totaal bedrag voor dit cart item
     * Accessor die quantity × price berekent voor gemakkelijke totaal weergave
     * 
     * @return float
     */
    public function getTotalAttribute(): float
    {
        return $this->quantity * $this->price;
    }
}