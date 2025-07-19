<?php

/**
 * Bestandsnaam: Promotion.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-01-29
 * Tijd: 00:09:32
 * Doel: Eloquent Model voor promoties/aanbiedingen in de webshop. Beheert marketing campagnes met gekoppelde producten en kortingsprijzen, inclusief soft delete voor historische data behoud.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    /**
     * Gebruik SoftDeletes trait voor veilige verwijdering
     * Promoties worden niet echt verwijderd maar gemarkeerd als deleted
     * Dit behoudt historische data voor rapportage en analyse doeleinden
     */
    use SoftDeletes;

    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'title',        // Titel van de promotie voor weergave
        'description',  // Beschrijving van de promotie en voorwaarden
        'cta_text',     // Call-to-Action tekst voor buttons/links
        'image_path',   // Pad naar promotie afbeelding in storage
        'is_active',    // Boolean om promotie zichtbaarheid te controleren
        'valid_until'   // Einddatum tot wanneer promotie geldig is
    ];

    /**
     * Attribute casting voor juiste data types
     * Zorgt voor automatische conversie tussen database en PHP types
     */
    protected $casts = [
        'is_active' => 'boolean',    // Actief status als boolean voor makkelijke controle
        'valid_until' => 'datetime'  // Vervaldatum als Carbon instance voor datumbewerkingen
    ];

    /**
     * Many-to-Many relatie naar producten die bij deze promotie betrokken zijn
     * Gebruikt pivot table om individuele kortingsprijzen per product op te slaan
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_products')
            ->withPivot('discount_price')  // Kortingsprijs per product uit pivot table
            ->withTimestamps();            // Timestamps wanneer product aan promotie toegevoegd
    }
}