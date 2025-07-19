<?php

/**
 * Bestandsnaam: 2025_02_02_112653_add_image_path_to_categories.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Database migration voor het uitbreiden van categories tabel met extra content velden. Voegt description en image_path kolommen toe voor rijkere categorie weergave met beschrijvingen en thumbnail afbeeldingen naast de bestaande banner functionaliteit.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Voegt description en image_path kolommen toe aan categories tabel voor uitgebreidere content
     * 
     * @return void
     */
    public function up(): void
    {
        // Wijzig categories tabel om extra content velden toe te voegen
        Schema::table('categories', function (Blueprint $table) {
            // Optionele beschrijving van de categorie voor SEO en gebruikersinformatie
            $table->string('description')->nullable();  // Korte beschrijving voor categorie overzichten
            
            // Optionaal pad naar categorie thumbnail/icon afbeelding
            $table->string('image_path')->nullable();   // Thumbnail afbeelding (aanvulling op bestaande banner_path)
            
            // Deze velden zijn nullable omdat:
            // - Bestaande categorieën geen verplichte migratie naar nieuwe velden hoeven
            // - Niet elke categorie heeft per se een beschrijving of aparte thumbnail nodig
            // - Banner_path en image_path kunnen verschillende doeleinden hebben (banner vs thumbnail)
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de description en image_path kolommen uit de categories tabel
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder description en image_path kolommen uit categories tabel
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['description', 'image_path']);
        });
    }
};