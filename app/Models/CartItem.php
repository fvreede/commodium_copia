<?php

/**
 * Bestandsnaam: CartItem.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-06-07
 * Tijd: 00:00:09
 * Doel: Eloquent Model voor winkelwagen items. Representeert individuele producten in de winkelwagen van gebruikers met hoeveelheid, prijs en automatische totaal berekening.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
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
    ];

    /**
     * Computed attributes die automatisch toegevoegd worden aan model output
     * Deze berekende velden zijn beschikbaar via $cartItem->total
     */
    protected $appends = ['total'];

    /**
     * Relatie naar de gebruiker die eigenaar is van dit winkelwagen item
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relatie naar het product dat in dit winkelwagen item zit
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
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