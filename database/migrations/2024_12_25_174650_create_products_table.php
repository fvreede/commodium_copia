<?php

/**
 * Bestandsnaam: 2024_12_25_174650_create_products_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-01-31
 * Tijd: 01:56:39
 * Doel: Database migration voor het aanmaken van de products tabel. Definieert de kern van de e-commerce catalogus met alle benodigde product informatie, prijzen, afbeeldingen en categorisatie via subcategorieën.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt de products tabel aan voor de complete productcatalogus van de webshop
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak products tabel aan voor alle webshop producten
        Schema::create('products', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke product identificatie
            
            // Foreign key naar subcategories tabel met cascade delete
            $table->foreignId('subcategory_id')
                ->constrained()              // Automatische foreign key constraint naar subcategories.id
                ->cascadeOnDelete();         // Verwijder producten als subcategorie wordt verwijderd
            
            $table->string('name');          // Productnaam voor weergave en zoeken
            $table->text('short_description'); // Korte productbeschrijving voor lijstweergaves
            $table->text('full_description');  // Uitgebreide productbeschrijving voor productpagina
            
            // Prijs met 8 digits totaal, 2 decimalen (max €999,999.99)
            $table->decimal('price', 8, 2);    // Productprijs in euro's met cent nauwkeurigheid
            
            $table->string('image_path');    // Pad naar hoofdproduct afbeelding in storage
            $table->timestamps();            // created_at en updated_at voor product tracking
            $table->softDeletes();           // deleted_at kolom voor soft delete functionaliteit
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de products tabel en alle bijbehorende product data
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder products tabel (gebruik dropIfExists voor veiligheid)
        Schema::dropIfExists('products');
    }
};