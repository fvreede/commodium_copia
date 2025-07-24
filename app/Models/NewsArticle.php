<?php

/**
 * Bestandsnaam: NewsArticle.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-07-24
 * Tijd: 21:15:00
 * Doel: Eloquent Model voor nieuwsartikelen op de website. Beheert nieuws content met publicatie status, afbeeldingen en publicatie timestamps voor content management.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * NewsArticle Model
 *
 * @property int $id De unieke identifier van het nieuwsartikel
 * @property string $title Titel van het nieuwsartikel
 * @property string|null $content Hoofdinhoud van het artikel (kan HTML bevatten)
 * @property string|null $image_path Pad naar uitgelichte afbeelding van het artikel
 * @property bool $is_published Boolean om te bepalen of artikel zichtbaar is voor gebruikers
 * @property \Illuminate\Support\Carbon|null $published_at Timestamp wanneer artikel gepubliceerd werd of wordt
 * @property \Illuminate\Support\Carbon $created_at Aanmaakdatum
 * @property \Illuminate\Support\Carbon $updated_at Laatste wijzigingsdatum
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle query()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsArticle whereUpdatedAt($value)
 * @method static NewsArticle|null find($id, $columns = ['*'])
 * @method static NewsArticle findOrFail($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|NewsArticle[] all($columns = ['*'])
 */
class NewsArticle extends Model
{
    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'news_articles';

    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'title',        // Titel van het nieuwsartikel
        'content',      // Hoofdinhoud van het artikel (kan HTML bevatten)
        'image_path',   // Pad naar uitgelichte afbeelding van het artikel
        'is_published', // Boolean om te bepalen of artikel zichtbaar is voor gebruikers
        'published_at'  // Timestamp wanneer artikel gepubliceerd werd of wordt
    ];

    /**
     * Attribute casting voor juiste data types
     * Zorgt voor automatische conversie tussen database en PHP types
     */
    protected $casts = [
        'is_published' => 'boolean',    // Publicatie status als boolean voor makkelijke controle
        'published_at' => 'datetime',   // Publicatie datum als Carbon instance voor datumbewerkingen
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}