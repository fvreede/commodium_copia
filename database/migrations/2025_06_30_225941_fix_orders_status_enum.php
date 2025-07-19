<?php

/**
 * Bestandsnaam: 2025_06_30_225941_fix_orders_status_enum.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Database migration voor het uitbreiden van order status enum waarden. Migreert bestaande data veilig en introduceert gedetailleerdere order lifecycle statussen voor betere tracking en klant communicatie in het e-commerce systeem.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Migreert bestaande order data en introduceert uitgebreide status enum waarden
     * 
     * @return void
     */
    public function up(): void
    {
        // STAP 1: Veilige data migratie VOOR schema wijziging
        // Migreer bestaande 'completed' status naar nieuwe 'delivered' status
        DB::table('orders')
            ->where('status', 'completed')
            ->update(['status' => 'delivered']);
        
        // Rationale voor deze migratie:
        // - 'completed' was te vaag (betaling of bezorging voltooid?)
        // - 'delivered' is expliciet: bestelling is fysiek bezorgd
        // - Voorkomt verwarring tussen payment completion en delivery completion

        // STAP 2: Werk de enum bij naar uitgebreide status waarden
        Schema::table('orders', function (Blueprint $table) {
            
            // Nieuwe uitgebreide enum met gedetailleerde order lifecycle
            $table->enum('status', [
                'pending',           // Bestelling geplaatst, wacht op bevestiging
                'confirmed',         // Bestelling bevestigd door systeem/admin, klaar voor verwerking
                'processing',        // Wordt gepickt/ingepakt in magazijn
                'out_for_delivery',  // Onderweg met bezorgdienst/koerier
                'delivered',         // Succesvol bezorgd bij klant
                'cancelled'          // Geannuleerd in elke fase van het proces
            ])
            ->default('pending')     // Nieuwe bestellingen starten als pending
            ->change();              // Wijzig bestaande kolom

            // Voeg database index toe voor betere query performance
            $table->index('status');
            
            // Voordelen van uitgebreide status enum:
            // 1. Betere klant communicatie (specifieke updates)
            // 2. Gedetailleerdere order tracking voor customer service
            // 3. Verbeterde logistics management
            // 4. Nauwkeurigere rapportage en analytics
            // 5. Duidelijke workflow stappen voor warehouse operaties
            
            // Status flow in praktijk:
            // pending → confirmed → processing → out_for_delivery → delivered
            //    ↓         ↓           ↓              ↓
            // cancelled  cancelled  cancelled   cancelled (emergency)
            
            // Performance index:
            // - Status wordt vaak gefilterd in queries
            // - Dashboard views groeperen op status
            // - Rapportage queries gebruiken status WHERE clauses
        });
    }

    /**
     * Draai de migrations terug
     * Herstelt originele enum waarden en verwijdert performance index
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            
            // Verwijder performance index
            $table->dropIndex(['status']);
            
            // Herstel naar originele enum waarden
            $table->enum('status', [
                'pending',          // Originele waarde behouden
                'processing',       // Originele waarde behouden  
                'completed',        // Hersteld van 'delivered' → 'completed'
                'cancelled'         // Originele waarde behouden
            ])->change();
        });
        
        // BELANGRIJK: Data migratie overwegingen bij rollback
        // 
        // Na rollback kunnen er problemen ontstaan:
        // 1. Orders met status 'confirmed' worden invalid (niet in originele enum)
        // 2. Orders met status 'out_for_delivery' worden invalid
        // 3. Orders met status 'delivered' blijven valid maar semantiek klopt niet
        // 
        // Aanbevolen manual cleanup na rollback:
        // - UPDATE orders SET status = 'processing' WHERE status = 'confirmed'
        // - UPDATE orders SET status = 'processing' WHERE status = 'out_for_delivery'
        // - UPDATE orders SET status = 'completed' WHERE status = 'delivered'
        // 
        // Of voer deze migration niet terug tenzij absoluut noodzakelijk
    }
};