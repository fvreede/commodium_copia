<?php

/**
 * Bestandsnaam: Subcategory.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.4
 * Datum: 2025-07-24
 * Tijd: 20:43:00
 * Doel: Eloquent Model voor subcategorieën in de webshop. Representeert de tweede niveau indeling tussen hoofdcategorieën en producten, inclusief soft delete voor data integriteit.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Subcategory Model
 *
 * @property int $id De unieke identifier van de subcategorie
 * @property int $category_id De ID van de hoofdcategorie
 * @property string $name De naam van de subcategorie
 * @property string|null $banner Optionale banner afbeelding voor subcategorie weergave
 * @property \Illuminate\Support\Carbon $created_at Aanmaakdatum
 * @property \Illuminate\Support\Carbon $updated_at Laatste wijzigingsdatum
 * @property \Illuminate\Support\Carbon|null $deleted_at Soft delete tijdstip
 * @property-read \App\Models\Category $category De hoofdcategorie
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory whereBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory whereDeletedAt($value)
 * @method static Subcategory|null find($id, $columns = ['*'])
 * @method static Subcategory findOrFail($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|Subcategory[] all($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Subcategory onlyTrashed()
 */
class Subcategory extends Model
{
    /**
     * Gebruik SoftDeletes trait voor veilige verwijdering
     * Subcategorieën worden niet echt verwijderd maar gemarkeerd als deleted
     * Dit behoudt data integriteit voor bestaande producten en navigatie structuur
     */
    use SoftDeletes;

    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'subcategories';

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
     * De attributen die gecast moeten worden naar specifieke types
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relatie naar de hoofdcategorie waar deze subcategorie bij hoort
     * Elke subcategorie behoort tot één hoofdcategorie (Many-to-One)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Category, \App\Models\Subcategory>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relatie naar alle producten die bij deze subcategorie horen
     * Een subcategorie kan meerdere producten hebben (One-to-Many)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}