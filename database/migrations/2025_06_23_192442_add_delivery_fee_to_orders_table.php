<?php

/**
 * Bestandsnaam: 2025_06_23_192442_add_delivery_fee_to_orders_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-06-24
 * Tijd: 20:49:53
 * Doel: Database migration voor het toevoegen van gedetailleerde kostenstructuur aan orders tabel. Voegt subtotal en delivery_fee kolommen toe voor transparante kostenopbouw en nauwkeurige financiële administratie in het e-commerce systeem.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Voegt subtotal en delivery_fee kolommen toe aan orders tabel voor gedetailleerde kostenstructuur
     * 
     * @return void
     */
    public function up(): void
    {
        // Wijzig orders tabel om gedetailleerde kostenstructuur toe te voegen
        Schema::table('orders', function (Blueprint $table) {
            
            // Voeg subtotal kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)
                    ->after('status')                    // Positioneer na status kolom voor logische volgorde
                    ->comment('Subtotal without delivery fee'); // Database comment voor documentatie
                
                // Subtotal = som van alle order items (quantity × price)
                // Excludeert bezorgkosten voor transparante kostenopbouw
                // Decimal (10,2) voor max €99,999,999.99 met cent nauwkeurigheid
            }
            
            // Voeg delivery_fee kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('orders', 'delivery_fee')) {
                $table->decimal('delivery_fee', 8, 2)
                    ->default(0)                         // Default 0 voor gratis bezorging of oude bestellingen
                    ->after('subtotal')                  // Positioneer na subtotal voor logische flow
                    ->comment('Delivery fee amount');    // Database comment voor documentatie
                
                // Delivery_fee = kosten voor bezorging gebaseerd op gekozen delivery_slot
                // Kan variëren per tijdslot, afstand, of express opties
                // Decimal (8,2) voor max €999,999.99 (voldoende voor bezorgkosten)
            }
            
            // Kostenstructuur logica na deze migration:
            // SUBTOTAL = Som van (order_items.quantity × order_items.price)
            // DELIVERY_FEE = Bezorgkosten uit delivery_slot.price
            // TOTAL = SUBTOTAL + DELIVERY_FEE
            // 
            // Voordelen van deze gedetailleerde structuur:
            // 1. Transparante kostenopbouw voor klanten
            // 2. Aparte tracking van product vs bezorgkosten
            // 3. Nauwkeurige BTW berekeningen (verschillende tarieven)
            // 4. Financiële rapportage en analytics
            // 5. Commissie berekeningen voor externe partners
            // 6. Promotie codes kunnen specifiek op subtotal werken
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert subtotal en delivery_fee kolommen uit orders tabel met veiligheidscontroles
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder subtotal en delivery_fee kolommen uit orders tabel
        Schema::table('orders', function (Blueprint $table) {
            
            // Controleer of subtotal kolom bestaat voordat verwijdering
            if (Schema::hasColumn('orders', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
            
            // Controleer of delivery_fee kolom bestaat voordat verwijdering
            if (Schema::hasColumn('orders', 'delivery_fee')) {
                $table->dropColumn('delivery_fee');
            }
            
            // Let op: Na rollback blijft alleen 'total' kolom over
            // Bestaande bestellingen behouden hun totaal bedrag
            // Maar gedetailleerde kostenopbouw gaat verloren
        });
    }
};