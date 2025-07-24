<?php

/**
 * Bestandsnaam: Product.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.6
 * Datum: 2025-07-24
 * Tijd: 20:50:00
 * Doel: Eloquent Model voor producten in de webshop. Beheert productinformatie, voorraad, prijzen, promoties en relaties met categorieën en bestellingen, inclusief soft delete voor data integriteit.
 */

// Enhanced Product Model
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Product Model
 *
 * @property int $id De unieke identifier van het product
 * @property int $subcategory_id ID van de subcategorie waar dit product bij hoort
 * @property string $name Productnaam voor weergave en zoeken
 * @property string|null $short_description Korte beschrijving voor productlijsten
 * @property string|null $full_description Uitgebreide beschrijving voor productpagina
 * @property float $price Basisprijs van het product (excl. eventuele kortingen)
 * @property string|null $image_path Pad naar productafbeelding in storage
 * @property int $stock_quantity Huidige voorraad van dit product
 * @property bool $is_active Boolean om product zichtbaarheid te controleren
 * @property \Illuminate\Support\Carbon $created_at Aanmaakdatum
 * @property \Illuminate\Support\Carbon $updated_at Laatste wijzigingsdatum
 * @property \Illuminate\Support\Carbon|null $deleted_at Soft delete tijdstip
 * @property-read \App\Models\Subcategory $subcategory De subcategorie waar dit product bij hoort
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Promotion> $promotions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSubcategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereFullDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStockQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDeletedAt($value)
 * @method static Product|null find($id, $columns = ['*'])
 * @method static Product findOrFail($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|Product[] all($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Product withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product onlyTrashed()
 */
class Product extends Model
{
    /**
     * Gebruik SoftDeletes trait voor veilige verwijdering
     * Producten worden niet echt verwijderd maar gemarkeerd als deleted
     * Dit behoudt data integriteit voor bestaande bestellingen en winkelwagens
     */
    use SoftDeletes;

    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'products';

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
        'stock_quantity' => 'integer',   // Voorraad als geheel getal
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relatie naar de subcategorie waar dit product bij hoort
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Subcategory, \App\Models\Product>
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Many-to-Many relatie naar promoties waar dit product bij betrokken is
     * Gebruikt pivot table om kortingsprijzen per promotie op te slaan
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Promotion>
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\OrderItem>
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
        if ($activePromotion && $activePromotion->pivot) {
            /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
            $pivot = $activePromotion->pivot;
            return $pivot->discount_price ?? $this->price;
        }
        
        return $this->price;
    }
}