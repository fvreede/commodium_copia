<?php

/**
 * Bestandsnaam: 2025_06_24_163029_add_house_number_to_user_addresses_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-06-24
 * Tijd: 20:49:53
 * Doel: Database migration voor het toevoegen van house_number kolom aan user_addresses tabel. Verbetert Nederlandse adres structuur door huisnummer te scheiden van straatnaam voor nauwkeurigere adres validatie en betere bezorglogistiek.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Voegt house_number kolom toe aan user_addresses tabel voor verfijnde Nederlandse adres structuur
     * 
     * @return void
     */
    public function up(): void
    {
        // Wijzig user_addresses tabel om house_number kolom toe te voegen
        Schema::table('user_addresses', function (Blueprint $table) {
            
            // Voeg house_number kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('user_addresses', 'house_number')) {
                $table->string('house_number')
                    ->nullable()                     // Nullable voor backwards compatibility met bestaande adressen
                    ->after('street');               // Positioneer na street kolom voor logische volgorde
                
                // Waarom house_number gescheiden van street:
                // 1. Nederlandse adres conventies: "Hoofdstraat" + "123A"
                // 2. Betere validatie mogelijkheden (numeriek + toevoeging)
                // 3. Nauwkeurigere bezorg routing algoritmes
                // 4. Compatibiliteit met PostNL en andere bezorgdiensten
                // 5. Gestructureerde data voor address validation APIs
                // 
                // Voorbeelden van Nederlandse huisnummers:
                // - "123" (basis huisnummer)
                // - "123A" (met letter toevoeging)
                // - "123-125" (nummerbereik)
                // - "123 bis" (met bis toevoeging)
                // - "123 rood" (met kleur aanduiding)
                
                // Na deze migration wordt adres structuur:
                // OLD: street = "Hoofdstraat 123A"
                // NEW: street = "Hoofdstraat" + house_number = "123A"
                
                // Nullable omdat:
                // - Bestaande adressen hebben huisnummer in street kolom
                // - Geleidelijke migratie mogelijk zonder data verlies
                // - Backwards compatibility met bestaande checkout flows
            }
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de house_number kolom uit user_addresses tabel
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder house_number kolom uit user_addresses tabel
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('house_number');
            
            // Let op na rollback:
            // - Huisnummer informatie gaat verloren als deze was ingevuld
            // - Bestaande adressen vallen terug op gecombineerde street veld
            // - Adres parsing functionaliteit moet aangepast worden
        });
    }
};