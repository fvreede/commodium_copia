<?php

/**
 * Bestandsnaam: UserAddress.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-06-24
 * Tijd: 20:49:53
 * Doel: Eloquent Model voor gebruikersadressen. Beheert bezorg- en factuuradressen van klanten met geformatteerde weergave functionaliteit voor checkout en order management.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'user_id',      // ID van de gebruiker die eigenaar is van dit adres
        'street',       // Straatnaam van het adres
        'house_number', // Huisnummer (inclusief toevoegingen zoals 12A)
        'city',         // Woonplaats
        'postal_code',  // Postcode in Nederlands formaat
        'country'       // Land (standaard Nederland voor lokale bezorging)
    ];

    /**
     * Relatie naar de gebruiker die eigenaar is van dit adres
     * Elk adres behoort tot één specifieke gebruiker (Many-to-One)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Krijg geformatteerde adres string voor weergave
     * 
     * Combineert alle adres elementen tot een leesbare string voor:
     * - Checkout overzichten
     * - Bestelling confirmaties  
     * - Bezorg labels
     * - Admin interfaces
     * 
     * @return string
     */
    public function getFormattedAddressAttribute(): string
    {
        // Begin met straatnaam
        $address = $this->street;

        // Voeg huisnummer toe indien aanwezig
        if ($this->house_number) {
            $address .= ' ' . $this->house_number;
        }

        // Voeg postcode en woonplaats toe (Nederlands formaat)
        $address .= ', ' . $this->postal_code . ' ' . $this->city;

        // Voeg land toe alleen als het niet Nederland is (voor internationale orders)
        if ($this->country && $this->country !== 'Netherlands') {
            $address .= ', ' . $this->country;
        }

        return $address;
    }
}