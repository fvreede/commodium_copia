<?php

/**
 * Bestandsnaam: Category.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.5
 * Datum: 2025-07-24
 * Tijd: 20:42:00
 * Doel: Eloquent Model voor hoofdcategorieën in de webshop. Representeert de hoofdindelingen van producten met bijbehorende subcategorieën, inclusief soft delete functionaliteit voor data integriteit.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Category Model
 *
 * @property int $id De unieke identifier van de categorie
 * @property string $name De naam van de categorie
 * @property string|null $description Beschrijving van de categorie
 * @property string|null $banner_path Het pad naar de banner afbeelding
 * @property string|null $image_path Het pad naar de categorie afbeelding  
 * @property \Illuminate\Support\Carbon $created_at Aanmaakdatum
 * @property \Illuminate\Support\Carbon $updated_at Laatste wijzigingsdatum
 * @property \Illuminate\Support\Carbon|null $deleted_at Soft delete tijdstip
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subcategory> $subcategories
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereBannerPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDeletedAt($value)
 * @method static Category|null find($id, $columns = ['*'])
 * @method static Category findOrFail($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|Category[] all($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Category withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Category withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Category onlyTrashed()
 */
class Category extends Model
{
    /**
     * Gebruik SoftDeletes trait voor veilige verwijdering
     * Categorieën worden niet echt verwijderd maar gemarkeerd als deleted
     * Dit behoudt data integriteit voor bestaande producten en bestellingen
     */
    use SoftDeletes;

    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'categories';

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
     * De attributen die gecast moeten worden naar specifieke types
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relatie naar alle subcategorieën die behoren tot deze hoofdcategorie
     * Een hoofdcategorie kan meerdere subcategorieën hebben (One-to-Many)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Subcategory>
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }
}