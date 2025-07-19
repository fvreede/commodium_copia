<?php

/**
 * Bestandsnaam: Subcategory.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-05-25
 * Tijd: 23:21:24
 * Doel: Eloquent Model voor subcategorieën in de webshop. Representeert de tweede niveau indeling tussen hoofdcategorieën en producten, inclusief soft delete voor data integriteit.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategory extends Model
{
    /**
     * Gebruik SoftDeletes trait voor veilige verwijdering
     * Subcategorieën worden niet echt verwijderd maar gemarkeerd als deleted
     * Dit behoudt data integriteit voor bestaande producten en navigatie structuur
     */
    use SoftDeletes;

    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'category_id',  // ID van de hoofdcategorie waar deze subcategorie bij hoort
        'name',         // Naam van de subcategorie (bijv. "Appels", "Wortelen")
        'banner'        // Optionale banner afbeelding voor subcategorie weergave
    ];

    /**
     * Relatie naar de hoofdcategorie waar deze subcategorie bij hoort
     * Elke subcategorie behoort tot één hoofdcategorie (Many-to-One)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relatie naar alle producten die bij deze subcategorie horen
     * Een subcategorie kan meerdere producten hebben (One-to-Many)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}