<?php

/**
 * Bestandsnaam: 2024_12_25_172333_create_categories_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-01-31
 * Tijd: 01:56:39
 * Doel: Database migration voor het aanmaken van de categories tabel. Definieert de hoofdcategorieën van de webshop productcatalogus met banner ondersteuning en soft delete functionaliteit voor data integriteit.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt de categories tabel aan voor hoofdcategorieën van producten
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak categories tabel aan voor productcatalogus hoofdindelingen
        Schema::create('categories', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke identificatie
            $table->string('name');          // Naam van de categorie (bijv. "Groenten", "Fruit", "Zuivel")
            $table->string('banner_path');   // Pad naar banner afbeelding voor categorie weergave
            $table->timestamps();            // created_at en updated_at voor record tracking
            $table->softDeletes();           // deleted_at kolom voor soft delete functionaliteit
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de categories tabel en alle bijbehorende data
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder categories tabel (gebruik dropIfExists voor veiligheid)
        Schema::dropIfExists('categories');
    }
};