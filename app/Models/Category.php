<?php

/**
 * Bestandsnaam: Category.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Eloquent Model voor hoofdcategorieën in de webshop. Representeert de hoofdindelingen van producten met bijbehorende subcategorieën, inclusief soft delete functionaliteit voor data integriteit.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    /**
     * Gebruik SoftDeletes trait voor veilige verwijdering
     * Categorieën worden niet echt verwijderd maar gemarkeerd als deleted
     * Dit behoudt data integriteit voor bestaande producten en bestellingen
     */
    use SoftDeletes;

    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'name',         // Naam van de categorie (bijv. "Groenten", "Fruit")
        'banner_path',  // Pad naar banner afbeelding voor categorie weergave
        'description',  // Optionele beschrijving van de categorie
        'image_path',   // Pad naar categorie afbeelding voor thumbnails
    ];

    /**
     * Relatie naar alle subcategorieën die behoren tot deze hoofdcategorie
     * Een hoofdcategorie kan meerdere subcategorieën hebben (One-to-Many)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }
}