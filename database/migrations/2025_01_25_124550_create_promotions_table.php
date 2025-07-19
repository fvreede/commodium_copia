<?php

/**
 * Bestandsnaam: 2025_01_25_124550_create_promotions_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Database migration voor het aanmaken van promotie systeem tabellen. Maakt promotions tabel aan voor marketing campagnes en promotion_products pivot tabel voor many-to-many relatie met individuele kortingsprijzen per product.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt promotions en promotion_products tabellen aan voor marketing campagne beheer
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak promotions tabel aan voor marketing campagnes en aanbiedingen
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke promotie identificatie
            $table->string('title');         // Titel van de promotie voor weergave (bijv. "Zomer Sale", "Black Friday")
            $table->text('description');     // Uitgebreide beschrijving van de promotie en voorwaarden
            $table->string('cta_text');      // Call-to-Action tekst voor buttons/links (bijv. "Shop Nu", "Bekijk Aanbieding")
            $table->string('image_path');    // Pad naar promotie banner/afbeelding in storage
            $table->boolean('is_active')->default(false); // Promotie actief/inactief status (standaard uitgeschakeld)
            $table->timestamp('valid_until')->nullable();  // Vervaldatum van de promotie (nullable voor permanente promoties)
            $table->softDeletes();           // deleted_at kolom voor soft delete (behoudt historische data)
            $table->timestamps();            // created_at en updated_at voor promotie tracking
        });

        // Maak promotion_products pivot tabel aan voor many-to-many relatie tussen promoties en producten
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke pivot record
            
            // Foreign key naar promotions tabel met cascade delete
            $table->foreignId('promotion_id')
                ->constrained()              // Automatische foreign key constraint naar promotions.id
                ->cascadeOnDelete();         // Verwijder product koppelingen als promotie wordt verwijderd
            
            // Foreign key naar products tabel met cascade delete
            $table->foreignId('product_id')
                ->constrained()              // Automatische foreign key constraint naar products.id
                ->cascadeOnDelete();         // Verwijder promotie koppelingen als product wordt verwijderd
            
            // Kortingsprijs voor dit specifieke product in deze promotie
            $table->decimal('discount_price', 8, 2); // Promotieprijs met 8 digits totaal, 2 decimalen (max €999,999.99)
            
            $table->timestamps();            // created_at en updated_at voor tracking wanneer producten toegevoegd werden
            
            // Optional: Voeg unique constraint toe om duplicaat product-promotie combinaties te voorkomen
            // $table->unique(['promotion_id', 'product_id']);
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert promotie tabellen in de juiste volgorde om foreign key problemen te voorkomen
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder tabellen in omgekeerde volgorde om foreign key constraints te respecteren
        Schema::dropIfExists('promotion_products'); // Eerst pivot tabel (heeft foreign keys naar promotions)
        Schema::dropIfExists('promotions');         // Dan promotions tabel (basis tabel)
    }
};