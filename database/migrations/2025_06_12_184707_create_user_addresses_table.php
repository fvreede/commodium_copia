<?php

/**
 * Bestandsnaam: 2025_06_12_184707_create_user_addresses_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-06-18
 * Tijd: 21:46:48
 * Doel: Database migration voor het aanmaken van de user_addresses tabel. Enables bezorgadres opslag voor gebruikers in het e-commerce systeem, met ondersteuning voor complete adres informatie en standaard Nederlandse markt focus.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt de user_addresses tabel aan voor gebruiker bezorgadres beheer
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak user_addresses tabel aan voor bezorgadres informatie van klanten
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke adres identificatie
            
            // Foreign key naar users tabel met cascade delete
            $table->foreignId('user_id')
                ->constrained()              // Automatische foreign key constraint naar users.id
                ->onDelete('cascade');       // Verwijder adressen automatisch als gebruiker wordt verwijderd
            
            // Adres componenten voor bezorging
            $table->string('street');        // Straatnaam voor bezorgadres (bijv. "Hoofdstraat 123")
            $table->string('city');          // Woonplaats/stad voor bezorging
            $table->string('postal_code');   // Postcode in Nederlands formaat (1234AB)
            $table->string('country')->default('Netherlands'); // Land met Nederlandse default voor lokale markt
            
            $table->timestamps();            // created_at en updated_at voor adres lifecycle tracking
            
            // Design overwegingen:
            // - Elke gebruiker kan meerdere adressen hebben (One-to-Many)
            // - Default land "Netherlands" omdat lokale webshop
            // - Street veld combineert straat + huisnummer voor simpliciteit
            // - Postal_code als string voor internationale flexibiliteit
            // - Cascade delete zorgt voor automatische cleanup
            
            // Mogelijke uitbreidingen voor de toekomst:
            // - house_number kolom gescheiden van street
            // - address_line_2 voor appartement/unit informatie
            // - is_default boolean voor standaard bezorgadres
            // - label kolom voor adres aliassen ("Thuis", "Werk", etc.)
            // - phone_number voor bezorg contactinformatie
            // - delivery_instructions voor speciale bezorginstructies
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de user_addresses tabel en alle bijbehorende adres data
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder user_addresses tabel (gebruik dropIfExists voor veiligheid)
        Schema::dropIfExists('user_addresses');
    }
};