<?php

/**
 * Bestandsnaam: 2025_02_04_150246_create_order_items_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Database migration voor het aanmaken van de order_items tabel. Representeert individuele producten binnen bestellingen met hoeveelheden en historische prijzen voor accurate order details en reportage, zelfs als product informatie later wijzigt.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt de order_items tabel aan voor gedetailleerde bestelling item administratie
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak order_items tabel aan voor individuele producten binnen bestellingen
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke order item identificatie
            
            // Foreign key naar orders tabel met cascade delete
            $table->foreignId('order_id')
                ->constrained()              // Automatische foreign key constraint naar orders.id
                ->onDelete('cascade');       // Verwijder order items automatisch als bestelling wordt verwijderd
            
            // Foreign key naar products tabel (geen cascade delete)
            $table->foreignId('product_id')
                ->constrained();             // Foreign key constraint naar products.id (behoudt relatie als product soft deleted wordt)
            
            // Hoeveelheid van dit product in de bestelling
            $table->integer('quantity');    // Aantal stuks besteld (moet positief geheel getal zijn)
            
            // Prijs per stuk ten tijde van bestelling (historische accuraatheid)
            $table->decimal('price', 10, 2); // Prijs met 10 digits totaal, 2 decimalen (max €99,999,999.99)
            // BELANGRIJK: Prijs wordt opgeslagen voor historische accuraatheid
            // Ook als product prijs later wijzigt, blijft bestelling correct
            
            $table->timestamps();            // created_at en updated_at voor order item tracking
            
            // Design overwegingen:
            // - Geen cascade delete op product_id: bestellingen blijven intact als producten verwijderd worden
            // - Price opslag voorkomt problemen bij prijswijzigingen
            // - Quantity als integer voor eenvoudige berekeningen
            // - Totaal per regel wordt berekend als quantity * price
            
            // Mogelijke uitbreidingen voor de toekomst:
            // - product_name kolom voor extra historische accuraatheid
            // - discount_amount kolom voor regel-specifieke kortingen
            // - tax_rate kolom voor BTW berekeningen
            // - notes kolom voor item-specifieke opmerkingen
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de order_items tabel en alle bijbehorende order item data
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder order_items tabel (gebruik dropIfExists voor veiligheid)
        Schema::dropIfExists('order_items');
    }
};