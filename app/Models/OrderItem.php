<?php

/**
 * Bestandsnaam: OrderItem.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-06-05
 * Tijd: 21:37:21
 * Doel: Eloquent Model voor individuele items binnen een bestelling. Representeert elk product in een bestelling met hoeveelheid, prijs en historische productnaam voor data integriteit bij product wijzigingen.
 */

// Enhanced OrderItem Model
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'order_id',      // ID van de bestelling waar dit item bij hoort
        'product_id',    // ID van het product (referentie voor relaties)
        'quantity',      // Aantal van dit product in de bestelling
        'price',         // Prijs per stuk ten tijde van bestelling (voor historische accuraatheid)
        'product_name'   // Productnaam opslaan voor het geval product wordt verwijderd
    ];

    /**
     * Attribute casting voor juiste data types
     * Zorgt voor automatische conversie tussen database en PHP types
     */
    protected $casts = [
        'price' => 'decimal:2',     // Prijs met 2 decimalen voor nauwkeurigheid
        'quantity' => 'integer'     // Hoeveelheid als geheel getal
    ];

    /**
     * Relatie naar de bestelling waar dit item bij hoort
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relatie naar het product (kan null zijn als product verwijderd is)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Bereken automatisch het regel totaal (hoeveelheid × prijs)
     * Accessor die line total berekent voor gemakkelijke weergave en berekeningen
     * 
     * @return float
     */
    public function getLineTotalAttribute(): float
    {
        return $this->quantity * $this->price;
    }
}