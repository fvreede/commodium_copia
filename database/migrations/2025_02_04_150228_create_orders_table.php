<?php

/**
 * Bestandsnaam: 2025_02_04_150228_create_orders_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Database migration voor het aanmaken van de orders tabel. Vormt de kern van het e-commerce bestelsysteem met gebruiker relaties, status tracking, totaal bedragen en bezorgslot koppeling voor complete order lifecycle management.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt de orders tabel aan voor het e-commerce bestelling management systeem
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak orders tabel aan voor centrale bestelling administratie
        Schema::create('orders', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke bestelling identificatie
            
            // Foreign key naar users tabel met cascade delete
            $table->foreignId('user_id')
                ->constrained()              // Automatische foreign key constraint naar users.id
                ->onDelete('cascade');       // Verwijder bestellingen als gebruiker wordt verwijderd
            
            // Totaal bedrag van de bestelling
            $table->decimal('total', 10, 2); // Bedrag met 10 digits totaal, 2 decimalen (max €99,999,999.99)
            
            // Status van de bestelling gedurende de lifecycle
            $table->enum('status', [
                'pending',                   // In afwachting - bestelling geplaatst maar nog niet bevestigd
                'processing',                // Wordt verwerkt - bestelling wordt voorbereid
                'completed',                 // Voltooid - bestelling is succesvol bezorgd
                'cancelled'                  // Geannuleerd - bestelling is geannuleerd door klant of systeem
            ]);
            
            // Optionele koppeling naar bezorgslot (nullable voor flexibiliteit)
            $table->foreignId('delivery_slot_id')
                ->nullable()                 // Nullable voor bestellingen zonder specifiek bezorgslot
                ->constrained();             // Foreign key constraint naar delivery_slots.id
            
            $table->timestamps();            // created_at en updated_at voor bestelling lifecycle tracking
            
            // Mogelijke uitbreidingen voor de toekomst:
            // - order_number kolom voor klant-vriendelijke bestelnummers
            // - subtotal, tax_amount, shipping_fee voor gedetailleerde kostenopbouw
            // - payment_status kolom voor betaling tracking
            // - delivery_address JSON kolom voor bezorgadres opslag
            // - notes kolom voor klant opmerkingen
            // - estimated_delivery_date voor planning
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de orders tabel en alle bijbehorende bestelling data
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder orders tabel (gebruik dropIfExists voor veiligheid)
        Schema::dropIfExists('orders');
    }
};