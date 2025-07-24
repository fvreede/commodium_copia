<?php

/**
 * Bestandsnaam: OrderItem.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.4
 * Datum: 2025-07-24
 * Tijd: 21:16:00
 * Doel: Eloquent Model voor individuele items binnen een bestelling. Representeert elk product in een bestelling met hoeveelheid, prijs en historische productnaam voor data integriteit bij product wijzigingen.
 */

// Enhanced OrderItem Model
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OrderItem Model
 *
 * @property int $id De unieke identifier van het order item
 * @property int $order_id ID van de bestelling waar dit item bij hoort
 * @property int $product_id ID van het product (referentie voor relaties)
 * @property int $quantity Aantal van dit product in de bestelling
 * @property float $price Prijs per stuk ten tijde van bestelling (voor historische accuraatheid)
 * @property string|null $product_name Productnaam opslaan voor het geval product wordt verwijderd
 * @property \Illuminate\Support\Carbon $created_at Aanmaakdatum
 * @property \Illuminate\Support\Carbon $updated_at Laatste wijzigingsdatum
 * @property-read \App\Models\Order $order De bestelling waar dit item bij hoort
 * @property-read \App\Models\Product|null $product Het product (kan null zijn als product verwijderd is)
 * @property-read float $line_total Berekent automatisch het regel totaal (hoeveelheid × prijs)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereUpdatedAt($value)
 * @method static OrderItem|null find($id, $columns = ['*'])
 * @method static OrderItem findOrFail($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|OrderItem[] all($columns = ['*'])
 */
class OrderItem extends Model
{
    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'order_items';

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
        'quantity' => 'integer',    // Hoeveelheid als geheel getal
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relatie naar de bestelling waar dit item bij hoort
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Order, \App\Models\OrderItem>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relatie naar het product (kan null zijn als product verwijderd is)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Product, \App\Models\OrderItem>
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