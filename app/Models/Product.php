<?php

/**
 * Bestandsnaam: Product.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.4
 * Datum: 2025-06-05
 * Tijd: 21:37:21
 * Doel: Eloquent Model voor producten in de webshop. Beheert productinformatie, voorraad, prijzen, promoties en relaties met categorieën en bestellingen, inclusief soft delete voor data integriteit.
 */

// Enhanced Product Model
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /**
     * Gebruik SoftDeletes trait voor veilige verwijdering
     * Producten worden niet echt verwijderd maar gemarkeerd als deleted
     * Dit behoudt data integriteit voor bestaande bestellingen en winkelwagens
     */
    use SoftDeletes;

    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'subcategory_id',    // ID van de subcategorie waar dit product bij hoort
        'name',              // Productnaam voor weergave en zoeken
        'short_description', // Korte beschrijving voor productlijsten
        'full_description',  // Uitgebreide beschrijving voor productpagina
        'price',             // Basisprijs van het product (excl. eventuele kortingen)
        'image_path',        // Pad naar productafbeelding in storage
        'stock_quantity',    // Huidige voorraad van dit product
        'is_active'          // Boolean om product zichtbaarheid te controleren
    ];

    /**
     * Attribute casting voor juiste data types
     * Zorgt voor automatische conversie tussen database en PHP types
     */
    protected $casts = [
        'price' => 'decimal:2',         // Prijs met 2 decimalen voor nauwkeurigheid
        'is_active' => 'boolean',       // Actief status als boolean voor makkelijke controle
        'stock_quantity' => 'integer'   // Voorraad als geheel getal
    ];

    /**
     * Relatie naar de subcategorie waar dit product bij hoort
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Many-to-Many relatie naar promoties waar dit product bij betrokken is
     * Gebruikt pivot table om kortingsprijzen per promotie op te slaan
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_products')
            ->withPivot('discount_price')  // Kortingsprijs uit pivot table
            ->withTimestamps();            // Wanneer product aan promotie toegevoegd
    }

    /**
     * Relatie naar alle bestelling items die dit product bevatten
     * Gebruikt voor verkoop statistieken en voorraad tracking
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Controleer of product op voorraad is voor gevraagde hoeveelheid
     * Controleert zowel actieve status als beschikbare voorraad
     * 
     * @param int $quantity Gevraagde hoeveelheid (standaard 1)
     * @return bool
     */
    public function isInStock($quantity = 1): bool
    {
        return $this->is_active && $this->stock_quantity >= $quantity;
    }

    /**
     * Krijg huidige prijs van product (inclusief actieve promotie indien van toepassing)
     * Controleert op actieve promoties en retourneert kortingsprijs of basisprijs
     * 
     * @return float
     */
    public function getCurrentPrice(): float
    {
        // Zoek naar actieve promotie die nu geldig is
        $activePromotion = $this->promotions()
            ->where('start_date', '<=', now())    // Promotie al gestart
            ->where('end_date', '>=', now())      // Promotie nog niet verlopen
            ->first();

        // Return kortingsprijs uit promotie of normale prijs
        return $activePromotion ? $activePromotion->pivot->discount_price : $this->price;
    }
}