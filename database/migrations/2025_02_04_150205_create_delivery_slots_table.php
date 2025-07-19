<?php

/**
 * Bestandsnaam: 2025_02_04_150205_create_delivery_slots_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Database migration voor het aanmaken van de delivery_slots tabel. Enables bezorgslot beheer voor het e-commerce systeem met datum, tijdvensters, prijzen en capaciteit planning voor geoptimaliseerde bezorglogistiek.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt de delivery_slots tabel aan voor bezorgslot planning en beheer
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak delivery_slots tabel aan voor bezorglogistiek en tijdslot beheer
        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke slot identificatie
            
            $table->date('date');            // Bezorgdatum voor dit tijdslot (YYYY-MM-DD formaat)
            $table->time('start_time');      // Starttijd van het bezorgvenster (HH:MM:SS formaat)
            $table->time('end_time');        // Eindtijd van het bezorgvenster (HH:MM:SS formaat)
            
            // Bezorgkosten voor dit specifieke tijdslot
            $table->decimal('price', 5, 2); // Prijs met 5 digits totaal, 2 decimalen (max €999.99)
            
            // Capaciteit management voor bezorgplanning
            $table->integer('available_slots'); // Aantal bestellingen dat dit tijdslot kan verwerken
            
            $table->timestamps();            // created_at en updated_at voor slot tracking
            
            // Mogelijke uitbreidingen voor de toekomst:
            // - status kolom (active, suspended, full)
            // - delivery_area_id voor verschillende bezorggebieden
            // - max_orders per slot voor extra capaciteit controle
            // - priority_fee voor premium tijdsloten
            // - recurring pattern ondersteuning (wekelijks, maandelijks)
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de delivery_slots tabel en alle bijbehorende bezorgslot data
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder delivery_slots tabel (gebruik dropIfExists voor veiligheid)
        Schema::dropIfExists('delivery_slots');
    }
};