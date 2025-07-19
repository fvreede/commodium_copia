<?php

/**
 * Bestandsnaam: 2025_06_05_151152_create_cart_items_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-06-05
 * Tijd: 21:37:21
 * Doel: Database migration voor het aanmaken van de cart_items tabel. Enables persistente winkelwagen opslag voor ingelogde gebruikers met product relaties, hoeveelheden en prijzen, als aanvulling op sessie-gebaseerde cart voor anonieme bezoekers.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt de cart_items tabel aan voor persistente winkelwagen opslag van ingelogde gebruikers
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak cart_items tabel aan voor database-opgeslagen winkelwagen items
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke cart item identificatie
            
            // Foreign key naar users tabel met cascade delete
            $table->foreignId('user_id')
                ->constrained()              // Automatische foreign key constraint naar users.id
                ->cascadeOnDelete();         // Verwijder cart items automatisch als gebruiker wordt verwijderd
            
            // Foreign key naar products tabel met cascade delete
            $table->foreignId('product_id')
                ->constrained()              // Automatische foreign key constraint naar products.id
                ->cascadeOnDelete();         // Verwijder cart items automatisch als product wordt verwijderd
            
            // Hoeveelheid van dit product in de winkelwagen
            $table->integer('quantity');    // Aantal stuks (moet positief geheel getal zijn)
            
            // Prijs opgeslagen op het moment van toevoegen aan winkelwagen
            $table->decimal('price', 8, 2); // Prijs met 8 digits totaal, 2 decimalen (max €999,999.99)
            // BELANGRIJK: Prijs wordt opgeslagen omdat:
            // - Promoties kunnen verlopen terwijl product in cart zit
            // - Product prijzen kunnen wijzigen tijdens shopping sessie
            // - Consistent pricing gedurende checkout proces
            // - Voorkomt verrassingen bij finale betaling
            
            $table->timestamps();            // created_at en updated_at voor cart item lifecycle tracking
            
            // Unique constraint voorkomt duplicate entries per gebruiker/product combinatie
            $table->unique(['user_id', 'product_id']); // Elke gebruiker kan slechts één entry per product hebben
            // Als gebruiker hetzelfde product nogmaals toevoegt, wordt quantity bijgewerkt
            // in plaats van nieuwe record aanmaken
            
            // Design overwegingen:
            // - Deze tabel is voor INGELOGDE gebruikers (persistent cart)
            // - Anonieme gebruikers gebruiken sessie-gebaseerde cart opslag
            // - Bij login wordt sessie cart gemigreerd naar deze database tabel
            // - Cascade deletes zorgen voor automatische cleanup
            // - Unique constraint voorkomt data duplicatie
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de cart_items tabel en alle bijbehorende winkelwagen data
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder cart_items tabel (gebruik dropIfExists voor veiligheid)
        Schema::dropIfExists('cart_items');
    }
};