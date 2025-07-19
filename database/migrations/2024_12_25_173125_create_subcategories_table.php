<?php

/**
 * Bestandsnaam: 2024_12_25_173125_create_subcategories_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-01-31
 * Tijd: 01:56:39
 * Doel: Database migration voor het aanmaken van de subcategories tabel. Definieert de tweede niveau indeling van de productcatalogus onder hoofdcategorieën, met foreign key relatie en cascade delete functionaliteit.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt de subcategories tabel aan voor tweede niveau productcatalogus indeling
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak subcategories tabel aan voor verfijnde productcatalogus indeling
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke identificatie
            
            // Foreign key naar categories tabel met cascade delete
            $table->foreignId('category_id')
                ->constrained()              // Automatische foreign key constraint naar categories.id
                ->cascadeOnDelete();         // Verwijder subcategorieën als hoofdcategorie wordt verwijderd
            
            $table->string('name');          // Naam van de subcategorie (bijv. "Appels", "Wortelen", "Melk")
            $table->timestamps();            // created_at en updated_at voor record tracking
            $table->softDeletes();           // deleted_at kolom voor soft delete functionaliteit
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de subcategories tabel en alle bijbehorende data
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder subcategories tabel (gebruik dropIfExists voor veiligheid)
        Schema::dropIfExists('subcategories');
    }
};