<?php

/**
 * Bestandsnaam: 2025_06_30_190512_enhance_orders_and_delivery_slots_for_checkout.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Database migration voor het uitbreiden van orders en delivery_slots tabellen. Voegt complete betaling tracking, order management velden en real-time delivery slot beschikbaarheid toe voor een volledig functioneel e-commerce systeem.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Verbetert orders en delivery_slots tabellen met betaling tracking en beschikbaarheid management
     * 
     * @return void
     */
    public function up(): void
    {
        // ORDERS TABEL UITBREIDINGEN
        Schema::table('orders', function (Blueprint $table) {
            
            // Voeg payment_method kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')
                    ->nullable()                 // Nullable voor bestaande bestellingen
                    ->after('total');            // Positioneer na total kolom
                
                // Ondersteunde betaalmethodes kunnen zijn:
                // - 'ideal' (Nederlandse iDEAL)
                // - 'creditcard' (Visa/Mastercard)
                // - 'paypal' (PayPal checkout)
                // - 'banktransfer' (Bankoverschrijving)
                // - 'cash' (Contant bij bezorging - als ondersteund)
            }
            
            // Voeg payment_status kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', [
                    'pending',               // In afwachting van betaling
                    'processing',            // Betaling wordt verwerkt door payment provider
                    'completed',             // Betaling succesvol voltooid
                    'failed',                // Betaling mislukt (onvoldoende saldo, geweigerd, etc.)
                    'cancelled'              // Betaling geannuleerd door klant of systeem
                ])
                ->default('pending')         // Nieuwe bestellingen starten als pending
                ->after('payment_method');   // Positioneer na payment_method voor logische flow
            }
            
            // Hernoem 'notes' naar 'order_notes' voor duidelijkheid, of voeg toe als het niet bestaat
            if (Schema::hasColumn('orders', 'notes') && !Schema::hasColumn('orders', 'order_notes')) {
                // Hernoem bestaande notes kolom naar order_notes
                $table->renameColumn('notes', 'order_notes');
            } elseif (!Schema::hasColumn('orders', 'order_notes')) {
                // Voeg order_notes toe als noch notes noch order_notes bestaat
                $table->text('order_notes')
                    ->nullable()             // Niet elke bestelling heeft opmerkingen
                    ->after('delivery_address');
                
                // Order notes kunnen bevatten:
                // - Klant opmerkingen ("Bel aan bij de buren")
                // - Speciale bezorginstructies
                // - Allergieën of dieet restricties
                // - Admin notities voor interne communicatie
            }
            
            // Voeg order_date kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('orders', 'order_date')) {
                $table->timestamp('order_date')
                    ->nullable()             // Nullable voor bestaande bestellingen
                    ->after('order_notes');  // Positioneer logisch na notes
                
                // Order_date = exacte datum/tijd wanneer bestelling geplaatst werd
                // Verschilt van created_at dat ook gebruikt wordt voor technische tracking
                // Kan gebruikt worden voor business analytics en klant communicatie
            }
            
            // Zorg ervoor dat order_number kolom bestaat en unique is
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')
                    ->unique()               // Moet uniek zijn voor klant referenties
                    ->nullable()             // Nullable voor bestaande bestellingen
                    ->after('id');           // Positioneer vroeg in tabel voor belangrijkheid
                
                // Order numbers volgen meestal formaat zoals:
                // "CC-2024-001234" (Commodum Copia - Jaar - Volgnummer)
                // Gebruiksvriendelijker dan database IDs voor klant communicatie
            }
        });
        
        // DELIVERY_SLOTS TABEL UITBREIDINGEN
        Schema::table('delivery_slots', function (Blueprint $table) {
            
            // Voeg current_available kolom toe voor real-time beschikbaarheid tracking
            if (!Schema::hasColumn('delivery_slots', 'current_available')) {
                $table->integer('current_available')
                    ->default(0)             // Start met 0, moet berekend worden
                    ->after('available_slots'); // Positioneer na available_slots voor vergelijking
                
                // Verschil tussen available_slots en current_available:
                // - available_slots = oorspronkelijke capaciteit van het slot
                // - current_available = real-time beschikbare plekken (available_slots - geboekte orders)
                // 
                // Voordelen van current_available kolom:
                // 1. Snellere queries voor slot beschikbaarheid (geen JOIN met orders nodig)
                // 2. Real-time updates bij order plaatsing/annulering
                // 3. Betere performance voor checkout flow
                // 4. Eenvoudiger cache management
                // 
                // Update logica:
                // - Bij order plaatsing: current_available--
                // - Bij order annulering: current_available++
                // - Periodieke synchronisatie met werkelijke order count
            }
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert toegevoegde kolommen (behalve renamed kolommen om data verlies te voorkomen)
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder toegevoegde kolommen uit orders tabel
        Schema::table('orders', function (Blueprint $table) {
            
            // Definieer kolommen die veilig verwijderd kunnen worden
            $columns = ['payment_method', 'payment_status', 'order_date'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // BELANGRIJK: We draaien de rename van 'notes' naar 'order_notes' NIET terug
            // omdat dit data verlies kan veroorzaken. Manual intervention nodig indien gewenst.
            // 
            // Order_number wordt ook niet verwijderd omdat dit referenties kan breken
            // die al gecommuniceerd zijn naar klanten
        });
        
        // Verwijder current_available kolom uit delivery_slots tabel
        Schema::table('delivery_slots', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_slots', 'current_available')) {
                $table->dropColumn('current_available');
            }
        });
    }
};