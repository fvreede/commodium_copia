<?php

/**
 * Bestandsnaam: UserAddress.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.5
 * Datum: 2025-07-24
 * Tijd: 19:41
 * Doel: Eloquent Model voor gebruikersadressen. Beheert bezorg- en factuuradressen van klanten met geformatteerde weergave functionaliteit voor checkout en order management.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserAddress Model
 *
 * @property int $id De unieke identifier van het adres
 * @property int $user_id De ID van de gebruiker die eigenaar is van dit adres
 * @property string $street Straatnaam van het adres
 * @property string $house_number Huisnummer (inclusief toevoegingen zoals 12A)
 * @property string $city Woonplaats
 * @property string $postal_code Postcode in Nederlands formaat
 * @property string $country Land (standaard Nederland voor lokale bezorging)
 * @property \Illuminate\Support\Carbon $created_at Aanmaakdatum
 * @property \Illuminate\Support\Carbon $updated_at Laatste wijzigingsdatum
 * @property-read \App\Models\User $user De gebruiker die eigenaar is van dit adres
 * @property-read string $formatted_address Geformatteerd adres voor weergave
 */
class UserAddress extends Model
{
    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'user_addresses';

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
     * De attributen die gecast moeten worden naar specifieke types
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
        if ($this->country && $this->country !== 'Netherlands' && $this->country !== 'Nederland') {
            $address .= ', ' . $this->country;
        }

        return $address;
    }
}