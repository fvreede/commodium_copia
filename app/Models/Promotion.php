<?php

/**
 * Bestandsnaam: Promotion.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-07-24
 * Tijd: 20:55:00
 * Doel: Eloquent Model voor promoties/aanbiedingen in de webshop. Beheert marketing campagnes met gekoppelde producten en kortingsprijzen, inclusief soft delete voor historische data behoud.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Promotion Model
 *
 * @property int $id De unieke identifier van de promotie
 * @property string $title Titel van de promotie voor weergave
 * @property string|null $description Beschrijving van de promotie en voorwaarden
 * @property string|null $cta_text Call-to-Action tekst voor buttons/links
 * @property string|null $image_path Pad naar promotie afbeelding in storage
 * @property bool $is_active Boolean om promotie zichtbaarheid te controleren
 * @property \Illuminate\Support\Carbon|null $valid_until Einddatum tot wanneer promotie geldig is
 * @property \Illuminate\Support\Carbon $created_at Aanmaakdatum
 * @property \Illuminate\Support\Carbon $updated_at Laatste wijzigingsdatum
 * @property \Illuminate\Support\Carbon|null $deleted_at Soft delete tijdstip
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read \Illuminate\Database\Eloquent\Relations\Pivot|null $pivot Pivot data voor many-to-many relaties
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereCtaText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereDeletedAt($value)
 * @method static Promotion|null find($id, $columns = ['*'])
 * @method static Promotion findOrFail($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|Promotion[] all($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion onlyTrashed()
 */
class Promotion extends Model
{
    /**
     * Gebruik SoftDeletes trait voor veilige verwijdering
     * Promoties worden niet echt verwijderd maar gemarkeerd als deleted
     * Dit behoudt historische data voor rapportage en analyse doeleinden
     */
    use SoftDeletes;

    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'promotions';

    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'title',       // Titel van de promotie voor weergave
        'description', // Beschrijving van de promotie en voorwaarden
        'cta_text',    // Call-to-Action tekst voor buttons/links
        'image_path',  // Pad naar promotie afbeelding in storage
        'is_active',   // Boolean om promotie zichtbaarheid te controleren
        'valid_until'  // Einddatum tot wanneer promotie geldig is
    ];

    /**
     * Attribute casting voor juiste data types
     * Zorgt voor automatische conversie tussen database en PHP types
     */
    protected $casts = [
        'is_active' => 'boolean',       // Actief status als boolean voor makkelijke controle
        'valid_until' => 'datetime',    // Vervaldatum als Carbon instance voor datumbewerkingen
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Many-to-Many relatie naar producten die bij deze promotie betrokken zijn
     * Gebruikt pivot table om individuele kortingsprijzen per product op te slaan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_products')
            ->withPivot('discount_price') // Kortingsprijs per product uit pivot table
            ->withTimestamps(); // Timestamps wanneer product aan promotie toegevoegd
    }
}