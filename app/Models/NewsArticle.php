<?php

/**
 * Bestandsnaam: NewsArticle.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-01-29
 * Tijd: 19:27:01
 * Doel: Eloquent Model voor nieuwsartikelen op de website. Beheert nieuws content met publicatie status, afbeeldingen en publicatie timestamps voor content management.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
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
        'published_at' => 'datetime'    // Publicatie datum als Carbon instance voor datumbewerkingen
    ];
}